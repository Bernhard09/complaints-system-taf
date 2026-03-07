<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ComplaintMessage;
use App\Models\Department;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        // ── Departments ──
        $departments = [];
        foreach (['FINANCE', 'COLLECTION', 'LEGAL', 'UMUM'] as $name) {
            $departments[$name] = Department::firstOrCreate(['name' => $name]);
        }

        // ── Supervisor (1 only) ──
        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@test.com'],
            ['name' => 'Supervisor', 'password' => Hash::make('password'), 'phone_number' => '+6281200000000', 'role' => 'SUPERVISOR']
        );

        // ── Agents (1 per department) ──
        $agents = [];
        $agentData = [
            'FINANCE'    => ['name' => 'Agent Finance',    'email' => 'agent.finance@test.com', 'phone' => '+6281234567890'],
            'COLLECTION' => ['name' => 'Agent Collection', 'email' => 'agent.collection@test.com', 'phone' => '+6281234567891'],
            'LEGAL'      => ['name' => 'Agent Legal',      'email' => 'agent.legal@test.com', 'phone' => '+6281234567892'],
            'UMUM'       => ['name' => 'Agent Umum',       'email' => 'agent.umum@test.com', 'phone' => '+6281234567893'],
        ];

        foreach ($agentData as $dept => $data) {
            $agents[$dept] = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'          => $data['name'],
                    'password'      => Hash::make('password'),
                    'role'          => 'AGENT',
                    'phone_number'  => $data['phone'],
                    'department_id' => $departments[$dept]->id,
                ]
            );
        }

        // ── Users (varied) ──
        $users = [];
        $userData = [
            ['name' => 'Budi Santoso',   'email' => 'budi@test.com',   'phone' => '+6282100000001'],
            ['name' => 'Siti Rahayu',    'email' => 'siti@test.com',   'phone' => '+6282100000002'],
            ['name' => 'Ahmad Fauzi',    'email' => 'ahmad@test.com',  'phone' => '+6282100000003'],
            ['name' => 'Dewi Lestari',   'email' => 'dewi@test.com',   'phone' => '+6282100000004'],
            ['name' => 'Rizky Pratama',  'email' => 'rizky@test.com',  'phone' => '+6282100000005'],
        ];

        foreach ($userData as $data) {
            $users[] = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Hash::make('password'), 'phone_number' => $data['phone'], 'role' => 'USER']
            );
        }

        // ── Complaint Scenarios ──
        $now = now();

        $scenarios = [

            // ── 1. SUBMITTED (baru masuk, belum di-assign) ──
            [
                'user'     => $users[0],
                'contract' => '1234567890',
                'reason'   => 'Tagihan ganda bulan Januari',
                'desc'     => 'Saya menerima dua tagihan untuk bulan yang sama. Mohon dicek dan dikoreksi.',
                'dept'     => 'FINANCE',
                'status'   => 'SUBMITTED',
                'agent'    => null,
                'created'  => $now->copy()->subHours(2),
            ],

            // ── 2. SUBMITTED (belum di-assign, dari user lain) ──
            [
                'user'     => $users[1],
                'contract' => '2345678901',
                'reason'   => 'Denda keterlambatan tidak wajar',
                'desc'     => 'Saya sudah bayar tepat waktu tapi tetap kena denda.',
                'dept'     => 'COLLECTION',
                'status'   => 'SUBMITTED',
                'agent'    => null,
                'created'  => $now->copy()->subHours(1),
            ],

            // ── 3. ASSIGNED (sudah di-assign, belum diproses) ──
            [
                'user'     => $users[2],
                'contract' => '3456789012',
                'reason'   => 'Perubahan jadwal angsuran',
                'desc'     => 'Saya ingin mengubah jadwal angsuran dari tanggal 5 ke tanggal 15.',
                'dept'     => 'FINANCE',
                'status'   => 'ASSIGNED',
                'agent'    => 'FINANCE',
                'created'  => $now->copy()->subDays(1),
                'assigned' => $now->copy()->subHours(20),
                'sla_resp' => $now->copy()->subHours(20)->addMinutes(30),
                'sla_res'  => $now->copy()->subHours(20)->addDays(3),
            ],

            // ── 4. ASSIGNED, Response SLA breached (agent belum respon) ──
            [
                'user'     => $users[3],
                'contract' => '4567890123',
                'reason'   => 'Bunga tidak sesuai kontrak',
                'desc'     => 'Bunga yang dikenakan lebih tinggi dari yang tertera di kontrak awal.',
                'dept'     => 'FINANCE',
                'status'   => 'ASSIGNED',
                'agent'    => 'FINANCE',
                'created'  => $now->copy()->subDays(2),
                'assigned' => $now->copy()->subDays(2),
                'sla_resp' => $now->copy()->subDays(2)->addMinutes(30), // already past
                'sla_res'  => $now->copy()->subDays(2)->addDays(3),
                'escalation' => 'ESCALATION_L1',
            ],

            // ── 5. IN_PROGRESS (agent sedang mengerjakan) ──
            [
                'user'     => $users[0],
                'contract' => '1234567890',
                'reason'   => 'Pembayaran tidak tercatat',
                'desc'     => 'Saya sudah transfer tapi pembayaran belum tercatat di sistem.',
                'dept'     => 'COLLECTION',
                'status'   => 'IN_PROGRESS',
                'agent'    => 'COLLECTION',
                'created'  => $now->copy()->subDays(1)->subHours(6),
                'assigned' => $now->copy()->subDays(1),
                'response' => $now->copy()->subDays(1)->addMinutes(15),
                'sla_resp' => $now->copy()->subDays(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Saya sudah transfer via ATM tanggal 20. Bukti terlampir.', 'ago' => 25],
                    ['sender' => 'AGENT', 'msg' => 'Baik, kami sedang memverifikasi pembayaran Anda. Mohon tunggu.', 'ago' => 20],
                ],
            ],

            // ── 6. WAITING_USER (menunggu respons user) ──
            [
                'user'     => $users[4],
                'contract' => '5678901234',
                'reason'   => 'Perselisihan jumlah pelunasan',
                'desc'     => 'Jumlah pelunasan yang diminta tidak sesuai dengan kalkulasi saya.',
                'dept'     => 'FINANCE',
                'status'   => 'WAITING_USER',
                'agent'    => 'FINANCE',
                'created'  => $now->copy()->subDays(3),
                'assigned' => $now->copy()->subDays(3)->addHours(1),
                'response' => $now->copy()->subDays(3)->addHours(2),
                'sla_resp' => $now->copy()->subDays(3)->addHours(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(3)->addHours(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Bisa tolong jelaskan perhitungannya?', 'ago' => 60],
                    ['sender' => 'AGENT', 'msg' => 'Berikut rincian pelunasan. Mohon cek dan konfirmasi apakah ada perbedaan.', 'ago' => 50],
                    ['sender' => 'AGENT', 'msg' => 'Apakah ada pertanyaan lanjutan? Kami tunggu respons Anda.', 'ago' => 30],
                ],
            ],

            // ── 7. IN_PROGRESS, Resolution SLA breached ──
            [
                'user'     => $users[1],
                'contract' => '2345678901',
                'reason'   => 'Surat peringatan salah alamat',
                'desc'     => 'Saya menerima surat peringatan padahal sudah pindah alamat dan sudah melapor.',
                'dept'     => 'LEGAL',
                'status'   => 'IN_PROGRESS',
                'agent'    => 'LEGAL',
                'created'  => $now->copy()->subDays(5),
                'assigned' => $now->copy()->subDays(5)->addHours(2),
                'response' => $now->copy()->subDays(5)->addHours(3),
                'sla_resp' => $now->copy()->subDays(5)->addHours(2)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(5)->addHours(2)->addDays(3), // breached (past)
                'escalation' => 'ESCALATION_L2',
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Surat peringatan dikirim ke alamat lama.', 'ago' => 100],
                    ['sender' => 'AGENT', 'msg' => 'Kami akan koordinasi dengan tim terkait untuk update alamat.', 'ago' => 90],
                    ['sender' => 'AGENT', 'msg' => 'Masih dalam proses verifikasi. Mohon bersabar.', 'ago' => 40],
                ],
            ],

            // ── 8. WAITING_CONFIRMATION (agent sudah resolve, tunggu user confirm) ──
            [
                'user'     => $users[2],
                'contract' => '3456789012',
                'reason'   => 'Koreksi data nasabah',
                'desc'     => 'Nama saya salah di sistem, mohon diperbaiki.',
                'dept'     => 'UMUM',
                'status'   => 'WAITING_CONFIRMATION',
                'agent'    => 'UMUM',
                'created'  => $now->copy()->subDays(2),
                'assigned' => $now->copy()->subDays(2)->addHours(1),
                'response' => $now->copy()->subDays(2)->addHours(2),
                'sla_resp' => $now->copy()->subDays(2)->addHours(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(2)->addHours(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Nama saya tertulis "Achmad" seharusnya "Ahmad". Mohon dikoreksi.', 'ago' => 45],
                    ['sender' => 'AGENT', 'msg' => 'Sudah kami koreksi di sistem. Mohon dicek dan konfirmasi.', 'ago' => 30],
                ],
            ],

            // ── 9. RESOLVED (selesai, sudah dikonfirmasi user) ──
            [
                'user'     => $users[3],
                'contract' => '4567890123',
                'reason'   => 'Permintaan surat keterangan lunas',
                'desc'     => 'Kredit saya sudah lunas. Mohon diterbitkan surat keterangan lunas.',
                'dept'     => 'FINANCE',
                'status'   => 'RESOLVED',
                'agent'    => 'FINANCE',
                'created'  => $now->copy()->subDays(7),
                'assigned' => $now->copy()->subDays(7)->addHours(1),
                'response' => $now->copy()->subDays(7)->addHours(2),
                'resolved' => $now->copy()->subDays(5),
                'sla_resp' => $now->copy()->subDays(7)->addHours(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(7)->addHours(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Mohon diterbitkan surat lunas.', 'ago' => 160],
                    ['sender' => 'AGENT', 'msg' => 'Surat keterangan lunas sudah kami proses. Bisa diambil di kantor cabang.', 'ago' => 130],
                    ['sender' => 'USER', 'msg' => 'Terima kasih banyak.', 'ago' => 120],
                ],
            ],

            // ── 10. RESOLVED (hari ini) ──
            [
                'user'     => $users[4],
                'contract' => '5678901234',
                'reason'   => 'Penambahan fasilitas',
                'desc'     => 'Saya ingin menambah limit kartu kredit.',
                'dept'     => 'FINANCE',
                'status'   => 'RESOLVED',
                'agent'    => 'FINANCE',
                'created'  => $now->copy()->subDays(2),
                'assigned' => $now->copy()->subDays(2)->addHours(1),
                'response' => $now->copy()->subDays(2)->addHours(3),
                'resolved' => $now->copy()->subHours(3),
                'sla_resp' => $now->copy()->subDays(2)->addHours(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(2)->addHours(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Bisa dibantu naikkan limit?', 'ago' => 50],
                    ['sender' => 'AGENT', 'msg' => 'Sudah kami proses. Limit baru akan aktif dalam 1x24 jam.', 'ago' => 10],
                ],
            ],

            // ── 11. IN_PROGRESS (Collection department) ──
            [
                'user'     => $users[0],
                'contract' => '1234567890',
                'reason'   => 'Keberatan penagihan',
                'desc'     => 'Tim penagihan datang ke rumah padahal saya sudah bayar. Mohon dicek.',
                'dept'     => 'COLLECTION',
                'status'   => 'IN_PROGRESS',
                'agent'    => 'COLLECTION',
                'created'  => $now->copy()->subDays(1)->subHours(3),
                'assigned' => $now->copy()->subDays(1),
                'response' => $now->copy()->subDays(1)->addMinutes(20),
                'sla_resp' => $now->copy()->subDays(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(1)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Tim penagihan datang lagi hari ini. Saya sudah bayar!', 'ago' => 20],
                    ['sender' => 'AGENT', 'msg' => 'Mohon maaf atas ketidaknyamanannya. Kami sedang cek pembayaran Anda.', 'ago' => 15],
                ],
            ],

            // ── 12. SUBMITTED (Legal) ──
            [
                'user'     => $users[3],
                'contract' => '6789012345',
                'reason'   => 'Masalah kontrak pinjaman',
                'desc'     => 'Klausul bunga dalam kontrak tidak sesuai dengan yang dijanjikan marketing.',
                'dept'     => 'LEGAL',
                'status'   => 'SUBMITTED',
                'agent'    => null,
                'created'  => $now->copy()->subMinutes(45),
            ],

            // ── 13. ASSIGNED (Umum, SLA masih aman) ──
            [
                'user'     => $users[4],
                'contract' => '7890123456',
                'reason'   => 'Permohonan informasi produk',
                'desc'     => 'Saya ingin tahu detail produk KPR terbaru.',
                'dept'     => 'UMUM',
                'status'   => 'ASSIGNED',
                'agent'    => 'UMUM',
                'created'  => $now->copy()->subHours(4),
                'assigned' => $now->copy()->subHours(3),
                'sla_resp' => $now->copy()->subHours(3)->addMinutes(30),
                'sla_res'  => $now->copy()->subHours(3)->addDays(3),
            ],

            // ── 14. IN_PROGRESS (L3 escalation, severely breached) ──
            [
                'user'     => $users[1],
                'contract' => '8901234567',
                'reason'   => 'Penolakan restrukturisasi',
                'desc'     => 'Pengajuan restrukturisasi saya ditolak tanpa alasan jelas.',
                'dept'     => 'COLLECTION',
                'status'   => 'IN_PROGRESS',
                'agent'    => 'COLLECTION',
                'created'  => $now->copy()->subDays(8),
                'assigned' => $now->copy()->subDays(8)->addHours(1),
                'response' => $now->copy()->subDays(8)->addHours(2),
                'sla_resp' => $now->copy()->subDays(8)->addHours(1)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(8)->addHours(1)->addDays(3), // breached > 24h
                'escalation' => 'ESCALATION_L3',
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Kenapa restrukturisasi saya ditolak?', 'ago' => 180],
                    ['sender' => 'AGENT', 'msg' => 'Kami sedang review ulang pengajuan Anda.', 'ago' => 150],
                    ['sender' => 'USER', 'msg' => 'Sudah seminggu belum ada kejelasan.', 'ago' => 48],
                    ['sender' => 'AGENT', 'msg' => 'Mohon maaf, kasus Anda sedang dievaluasi tim manajemen.', 'ago' => 24],
                ],
            ],

            // ── 15. RESOLVED (Collection, resolved yesterday) ──
            [
                'user'     => $users[2],
                'contract' => '9012345678',
                'reason'   => 'Salah nominal penagihan',
                'desc'     => 'Nominal tagihan tidak sesuai dengan jadwal angsuran.',
                'dept'     => 'COLLECTION',
                'status'   => 'RESOLVED',
                'agent'    => 'COLLECTION',
                'created'  => $now->copy()->subDays(4),
                'assigned' => $now->copy()->subDays(4)->addHours(2),
                'response' => $now->copy()->subDays(4)->addHours(3),
                'resolved' => $now->copy()->subDays(1),
                'sla_resp' => $now->copy()->subDays(4)->addHours(2)->addMinutes(30),
                'sla_res'  => $now->copy()->subDays(4)->addHours(2)->addDays(3),
                'messages' => [
                    ['sender' => 'USER', 'msg' => 'Tagihan harusnya 2 juta, tapi muncul 2.5 juta.', 'ago' => 90],
                    ['sender' => 'AGENT', 'msg' => 'Sudah kami koreksi. Nominal yang benar adalah Rp 2.000.000.', 'ago' => 48],
                    ['sender' => 'USER', 'msg' => 'Sudah benar sekarang. Terima kasih.', 'ago' => 24],
                ],
            ],
        ];

        // ── Create complaints & messages ──
        foreach ($scenarios as $s) {
            $complaint = Complaint::create([
                'user_id'                => $s['user']->id,
                'contract_number'        => $s['contract'],
                'complaint_reason'       => $s['reason'],
                'description'            => $s['desc'],
                'department_id'          => $departments[$s['dept']]->id,
                'agent_id'               => $s['agent'] ? $agents[$s['agent']]->id : null,
                'status'                 => $s['status'],
                'assigned_by'            => $s['agent'] ? $supervisor->id : null,
                'assigned_at'            => $s['assigned'] ?? null,
                'first_response_at'      => $s['response'] ?? null,
                'resolved_at'            => $s['resolved'] ?? null,
                'sla_response_deadline'  => $s['sla_resp'] ?? null,
                'sla_resolution_deadline'=> $s['sla_res'] ?? null,
                'escalation_level'       => $s['escalation'] ?? null,
                'escalated_at'           => isset($s['escalation']) ? ($s['assigned'] ?? $s['created']) : null,
                'created_at'             => $s['created'],
                'updated_at'             => $s['resolved'] ?? $s['created'],
            ]);

            // Create messages
            if (!empty($s['messages'])) {
                foreach ($s['messages'] as $m) {
                    $senderId = $m['sender'] === 'USER'
                        ? $s['user']->id
                        : ($s['agent'] ? $agents[$s['agent']]->id : $s['user']->id);

                    ComplaintMessage::create([
                        'complaint_id' => $complaint->id,
                        'sender_id'    => $senderId,
                        'sender_role'  => $m['sender'],
                        'message'      => $m['msg'],
                        'created_at'   => $now->copy()->subMinutes($m['ago']),
                        'updated_at'   => $now->copy()->subMinutes($m['ago']),
                    ]);
                }
            }

            // Generate notification for user
            if ($s['status'] !== 'SUBMITTED') {
                Notification::create([
                    'user_id'    => $s['user']->id,
                    'type'       => $s['status'] === 'RESOLVED' ? 'success' : 'info',
                    'title'      => $this->notifTitle($s['status'], $complaint->id),
                    'message'    => $this->notifMessage($s['status'], $complaint->id),
                    'link'       => '/complaints/' . $complaint->id,
                    'is_read'    => $s['status'] === 'RESOLVED',
                    'created_at' => $s['assigned'] ?? $s['created'],
                    'updated_at' => $s['assigned'] ?? $s['created'],
                ]);
            }
        }
    }

    private function notifTitle(string $status, int $id): string
    {
        return match ($status) {
            'ASSIGNED'                => "Agent Assigned to #{$id}",
            'IN_PROGRESS'             => "Complaint #{$id} In Progress",
            'WAITING_USER'            => "Response Needed",
            'WAITING_CONFIRMATION'    => "Confirm Resolution #{$id}",
            'RESOLVED'                => "Complaint #{$id} Resolved",
            default                   => "Update on #{$id}",
        };
    }

    private function notifMessage(string $status, int $id): string
    {
        return match ($status) {
            'ASSIGNED'                => "An agent has been assigned to handle your complaint #{$id}.",
            'IN_PROGRESS'             => "Your complaint #{$id} is now being processed.",
            'WAITING_USER'            => "Agent is waiting for your response on complaint #{$id}.",
            'WAITING_CONFIRMATION'    => "Your complaint #{$id} has been resolved. Please confirm.",
            'RESOLVED'                => "Your complaint #{$id} has been resolved. Thank you!",
            default                   => "There is an update on your complaint #{$id}.",
        };
    }
}
