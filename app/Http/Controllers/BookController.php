<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Library catalogue. */
class BookController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $books = Book::withAvailability()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")))
            ->sortable([
                'title' => 'title', 'category' => 'category', 'isbn' => 'isbn', 'shelf' => 'shelf', 'copies' => 'quantity',
            ], 'title')
            ->paginateFromRequest(20);

        return view('library.books.index', compact('books', 'search'));
    }

    public function create()
    {
        return view('library.books.form', ['book' => null]);
    }

    public function store(Request $request)
    {
        Book::create($this->validated($request));

        return redirect()->route('books.index')->with('success', 'Book added.');
    }

    public function edit(Book $book)
    {
        return view('library.books.form', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $this->validated($request, $book);
        $onLoan = $book->activeLoans()->count();
        if ($validated['quantity'] < $onLoan) {
            return back()->withInput()->withErrors(['quantity' => "{$onLoan} copies are on loan; the quantity cannot be lower."]);
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Book updated.');
    }

    public function destroy(Book $book)
    {
        if ($book->loans()->exists()) {
            return back()->with('error', 'This book has loan history and cannot be deleted. Set its quantity instead.');
        }

        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book deleted.');
    }

    private function validated(Request $request, ?Book $ignore = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books')->ignore($ignore?->id)],
            'category' => 'nullable|string|max:100',
            'publisher' => 'nullable|string|max:255',
            'shelf' => 'nullable|string|max:50',
            'quantity' => 'required|integer|min:0|max:10000',
        ]);
    }
}
