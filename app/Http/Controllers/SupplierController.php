<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::query()->withCount('products')->orderBy('name')->paginate(15);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::query()->create($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur cree.');
    }

    public function edit(Supplier $supplier): View
    {
        abort_if($supplier->status !== RecordStatus::Active, 403);

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        abort_if($supplier->status !== RecordStatus::Active, 403);
        $supplier->update($this->validated($request));

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur mis a jour.');
    }

    public function archive(Supplier $supplier): RedirectResponse
    {
        $supplier->update(['status' => RecordStatus::Archived]);

        return redirect()->route('suppliers.index')->with('success', 'Fournisseur archive.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
