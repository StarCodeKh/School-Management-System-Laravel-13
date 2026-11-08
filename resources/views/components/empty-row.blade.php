@props(['colspan', 'message' => 'No records found.'])

<tr>
    <td colspan="{{ $colspan }}" class="py-10! text-center text-slate-500">{{ $message }}</td>
</tr>
