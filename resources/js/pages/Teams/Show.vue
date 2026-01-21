<script setup lang="ts">
import DeleteTeamForm from '@/pages/Teams/Partials/DeleteTeamForm.vue';
import TeamMemberManager from '@/pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/pages/Teams/Partials/UpdateTeamNameForm.vue';

import { Head, Link } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types/index';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';
import { Plus } from 'lucide-vue-next';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: edit().url,
    },
];

const props = defineProps<{
    team: any;
    availableRoles: any[];
    permissions: any;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Team" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Team"
                    description="Manage your team's settings"
                />

                <UpdateTeamNameForm :team :permissions="permissions" />

                <TeamMemberManager
                    :team
                    :available-roles="availableRoles"
                    :user-permissions="permissions"
                />

                <div class="space-y-6">
                    <HeadingSmall
                        title="Create New Team"
                        description="Create a new team to collaborate with others"
                    />
                    <div>
                        <Link :href="route('teams.create')">
                            <Button>
                                <Plus class="mr-2 h-4 w-4" />
                                Create Team
                            </Button>
                        </Link>
                    </div>
                </div>

                <template v-if="permissions.canDeleteTeam && !team.personal_team">
                    <DeleteTeamForm :team />
                </template>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
