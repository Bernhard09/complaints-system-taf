<div class="overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
        <thead>
        <tr>
            <th>ID</th>
            <th>Contract</th>
            <th>Reason</th>
            <th>User</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Created</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach($complaints as $complaint)
            <tr>
                <td>#{{ $complaint->id }}</td>
                <td>{{ $complaint->contract_number }}</td>
                <td>{{ $complaint->complaint_reason }}</td>
                <td>{{ $complaint->user->name }}</td>
                <td>{{ $complaint->status }}</td>
                <td>{{ optional($complaint->agent)->name ?? '-' }}</td>
                <td>{{ $complaint->created_at->diffForHumans() }}</td>
                <td>
                    <a href="{{ route('supervisor.complaints.show', $complaint) }}">
                        View
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>
