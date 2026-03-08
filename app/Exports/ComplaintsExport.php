<?php

namespace App\Exports;

use App\Models\Complaint;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ComplaintsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?int $agentId;

    /**
     * @param int|null $agentId  Pass agent ID to filter, or null for all complaints.
     */
    public function __construct(?int $agentId = null)
    {
        $this->agentId = $agentId;
    }

    public function collection()
    {
        $query = Complaint::with(['user', 'agent', 'department'])->latest();

        if ($this->agentId) {
            $query->forAgent($this->agentId);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Contract Number',
            'Complaint Reason',
            'Description',
            'User',
            'Agent',
            'Department',
            'Status',
            'SLA Status',
            'Created At',
            'Resolved At',
        ];
    }

    public function map($complaint): array
    {
        return [
            $complaint->id,
            $complaint->contract_number,
            $complaint->complaint_reason,
            $complaint->description,
            optional($complaint->user)->name ?? '-',
            optional($complaint->agent)->name ?? '-',
            optional($complaint->department)->name ?? '-',
            $complaint->status,
            $complaint->sla_status ?? '-',
            $complaint->created_at?->format('Y-m-d H:i:s'),
            $complaint->resolved_at?->format('Y-m-d H:i:s') ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
