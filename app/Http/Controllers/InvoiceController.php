<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\Customer;
use App\Actions\IssueInvoice;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $invoices = Invoice::with(['customer', 'invoiceItems'])
            ->latest()
            ->paginate(15);

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('invoices/Create', [
            'customers' => $customers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request)
    {
        $invoice = \DB::transaction(function () use ($request) {
            $validated = $request->validated();

            // Calculate total amount from items
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });

            // Create invoice
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'status' => $validated['status'],
                'amount' => $totalAmount,
                'issue_date' => $validated['issue_date'],
                'due_date' => $validated['due_date'],
                'payment_date' => $validated['payment_date'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            // Create invoice items
            foreach ($validated['items'] as $item) {
                $invoice->invoiceItems()->create([
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'invoiceItems', 'bankBillet']);

        return Inertia::render('invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load('invoiceItems');

        $customers = Customer::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('invoices/Edit', [
            'invoice' => $invoice,
            'customers' => $customers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $validated = $request->validated();

        // Calculate total amount from items if provided
        if (isset($validated['items'])) {
            $totalAmount = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['unit_price'];
            });
            $validated['amount'] = $totalAmount;
        }

        // Update invoice
        $invoice->update($validated);

        // Update invoice items if provided
        if (isset($validated['items'])) {
            // Delete old items
            $invoice->invoiceItems()->delete();

            // Create new items
            foreach ($validated['items'] as $item) {
                $invoice->invoiceItems()->create([
                    'title' => $item['title'],
                    'subtitle' => $item['subtitle'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'amount' => $item['quantity'] * $item['unit_price'],
                ]);
            }
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        $invoice->invoiceItems()->delete();
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Issue an invoice and generate bank billet
     */
    public function issue(Invoice $invoice, IssueInvoice $issueInvoice)
    {
        $issueInvoice->execute($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Invoice issued successfully.");

    }
}
