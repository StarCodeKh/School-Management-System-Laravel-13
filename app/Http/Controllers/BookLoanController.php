<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/** Issuing and returning library books. */
class BookLoanController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), ['active', 'overdue', 'returned'], true) ? $request->query('tab') : 'active';

        $loans = BookLoan::with(['book', 'student.schoolClass', 'student.section'])
            ->when($tab === 'active', fn ($q) => $q->active())
            ->when($tab === 'overdue', fn ($q) => $q->overdue())
            ->when($tab === 'returned', fn ($q) => $q->whereNotNull('returned_on'))
            ->orderBy($tab === 'returned' ? 'returned_on' : 'due_on', $tab === 'returned' ? 'desc' : 'asc')
            ->paginate(20)
            ->withQueryString();

        $counts = ['active' => BookLoan::active()->count(), 'overdue' => BookLoan::overdue()->count()];

        return view('library.loans.index', compact('loans', 'tab', 'counts'));
    }

    public function create(Request $request)
    {
        $books = Book::withAvailability()->orderBy('title')->get()->filter(fn (Book $book) => $book->available() > 0);
        $students = Student::with(['schoolClass', 'section'])->orderBy('first_name')->get();

        return view('library.loans.create', [
            'books' => $books->mapWithKeys(fn (Book $book) => [$book->id => $book->title.' ('.$book->available().' available)']),
            'students' => $students->mapWithKeys(fn (Student $student) => [$student->id => $student->full_name.' - '.$student->admission_id]),
            'bookId' => $request->integer('book_id') ?: null,
            'dueOn' => today()->addDays((int) setting('library_loan_days', 14)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:students,id',
            'issued_on' => 'required|date|before_or_equal:today',
            'due_on' => 'required|date|after_or_equal:issued_on',
        ]);

        $book = Book::withAvailability()->findOrFail($validated['book_id']);
        if ($book->available() < 1) {
            throw ValidationException::withMessages(['book_id' => 'No copies of this book are available.']);
        }
        if (BookLoan::active()->where('book_id', $book->id)->where('student_id', $validated['student_id'])->exists()) {
            throw ValidationException::withMessages(['student_id' => 'This student already has a copy of this book.']);
        }

        BookLoan::create($validated + ['issued_by' => $request->user()->id]);

        return redirect()->route('loans.index')->with('success', "Issued \"{$book->title}\".");
    }

    /** mark returned; a late return is charged the daily fine from Settings > Library */
    public function return(BookLoan $loan)
    {
        abort_if($loan->returned_on, 422, 'This book has already been returned.');

        $fine = $loan->daysLate(today()) * (float) setting('library_fine_per_day', 0);
        $loan->update(['returned_on' => today(), 'fine' => $fine]);

        return back()->with('success', $fine > 0 ? 'Returned late. Fine: '.money($fine) : 'Book returned.');
    }
}
