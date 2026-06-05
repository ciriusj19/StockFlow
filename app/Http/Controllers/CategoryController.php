<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->withCount('products')->orderBy('name')->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Category::query()->create($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Categorie creee.');
    }

    public function edit(Category $category): View
    {
        abort_if($category->status !== RecordStatus::Active, 403);

        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        abort_if($category->status !== RecordStatus::Active, 403);
        $category->update($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Categorie mise a jour.');
    }

    public function archive(Category $category): RedirectResponse
    {
        $category->update(['status' => RecordStatus::Archived]);

        return redirect()->route('categories.index')->with('success', 'Categorie archivee.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
