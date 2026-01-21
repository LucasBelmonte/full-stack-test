<?php

namespace Lib\Tenancy;

use Illuminate\Support\Arr;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobRetryRequested;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Events\TokenAuthenticated;
use Laravel\Sanctum\PersonalAccessToken;
use Lib\Tenancy\Events\MakingTenantCurrent;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Tenant::runningUnitTests(app()->runningUnitTests());

        // Quando um usuário é autenticado, define-se o tenant atual com o team_id do usuário.
        Event::listen(MakingTenantCurrent::class, function (MakingTenantCurrent $event) {
            Tenant::makeCurrent($event->tenantId(), $event->timezone);
        });

        PersonalAccessToken::creating(function (PersonalAccessToken $token) {
            if (is_null($token->team_id)) {
                $token->team_id = $token->tokenable->current_team_id;
            }
        });

        // --------------------------------------------------------------
        // No contexto web, o tenant é definido pelo time atual
        // que o usuário está logado.
        // --------------------------------------------------------------
        Event::listen(Authenticated::class, function (Authenticated $event) {
            if (! $event->user->current_team_id) {
                return;
            }
            event(new MakingTenantCurrent(
                $event->user,
                $event->user->current_team_id,
                $event->user->timezone || config('app.timezone')
            ));
        });

        // --------------------------------------------------------------
        // No context api, o tenant é definido pelo token de autenticação.
        // Se o token não tiver um team_id, o tenant é definido pelo tokenable
        // user.current_team_id.
        // --------------------------------------------------------------
        Event::listen(TokenAuthenticated::class, function (TokenAuthenticated $event) {
            // Tokens criados são limitados ao time que criou o token!
            MakingTenantCurrent::dispatch(
                $event->token->tokenable,
                $event->token->team_id,
                $event->token->tokenable->timezone || config('app.timezone')
            );
        });

        $this->configureRequest();
        $this->configureQueue();
    }

    // O tenant é definido quando um usuário é autenticado
    protected function configureRequest()
    {
        // Por padrão, o current tenant não tem acesso a nenhum model (team_id = 0)!
        // A menos que o tenant seja o landlord, e que é explicitamente configurado pelo método asLandlord.
        Tenant::makeCurrent(new TenantId(0));

        // Quando um usuário é deslogado, reseta-se o tenant atual.
        Event::listen(Logout::class, fn () => [Tenant::reset(), Tenant::makeCurrent(new TenantId(0))]);
    }

    // Adiciona o contexto do tenant (team_id) ao job
    protected function configureQueue()
    {
        // Quando o job é adicionado na fila, se ele for tenant aware e o tenant estiver habilitado, adiciona o tenant_id ao payload
        app('queue')->createPayloadUsing(function ($connectionName, $queue, $payload) {
            if (Tenant::exists() && Tenant::current()->__toString() !== '0') {
                return ['tenantId' => Tenant::current(), 'timezone' => Tenant::getTimezone()];
            }

            return [];
        });

        // Antes do job ser processado, define o contexto do tenant
        app('events')->listen([JobProcessing::class, JobRetryRequested::class], function (JobProcessing|JobRetryRequested $event) {
            $payload = $this->getEventPayload($event);

            if (array_key_exists('tenantId', $payload)) {
                Tenant::makeCurrent(new TenantId($payload['tenantId']), Arr::get($payload, 'timezone', 'UTC'));
            }
        });
    }

    protected function getEventPayload($event): ?array
    {
        return match (true) {
            $event instanceof JobProcessing => $event->job->payload(),
            $event instanceof JobRetryRequested => $event->payload(),
            default => null,
        };
    }
}
