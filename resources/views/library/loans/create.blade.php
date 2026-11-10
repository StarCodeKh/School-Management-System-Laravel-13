<x-layouts.app title="Issue Book">
    <x-page-header title="Issue Book" :breadcrumbs="['Book Loans' => route('loans.index'), 'Issue Book' => null]" />

    <x-card class="max-w-3xl">
        <form method="POST" action="{{ route('loans.store') }}" class="space-y-6">
            @csrf
            <x-form.section title="Loan" description="Which book goes to which student, and when it is due back." icon="library">
                <x-form.select name="book_id" label="Book" :options="$books" :value="$bookId" placeholder="Select book" required wrapper="sm:col-span-2"
                               :hint="$books->isEmpty() ? 'No copies available. Add books or wait for returns.' : null" />
                <x-form.select name="student_id" label="Student" :options="$students" placeholder="Select student" required wrapper="sm:col-span-2" />
                <x-form.input name="issued_on" type="date" label="Issued on" :value="today()" :max="today()->toDateString()" required />
                <x-form.input name="due_on" type="date" after="issued_on" label="Due on" :value="$dueOn" required :hint="'Default loan period: '.setting('library_loan_days').' days.'" />
            </x-form.section>
            <x-form.actions submit="Issue book" :cancel="route('loans.index')" />
        </form>
    </x-card>
</x-layouts.app>
