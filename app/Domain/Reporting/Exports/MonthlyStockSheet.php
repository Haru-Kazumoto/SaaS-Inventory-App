<?php

namespace App\Domain\Reporting\Exports;

use App\Domain\Reporting\Queries\MonthlyStockReportQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Ramsey\Collection\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/** Satu sheet laporan bulanan barang habis pakai (per kategori). */
class MonthlyStockSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithEvents
{
    private array $data;
    private array $days;

    public function __construct(
        public int $organizationId,
        public ?int $unitId,
        public int $year,
        public int $month,
        public ?int $categoryId = null,
        public ?string $categoryName = null,
    ) {
        $this->data = app(MonthlyStockReportQuery::class)->build(
            organizationId: $this->organizationId,
            unitId: $this->unitId,
            year: $this->year,
            month: $this->month,
            categoryId: $this->categoryId,
        );
        $this->days = $this->data['days'] ?? [];
    }

    public function headings(): array
    {
        $h = ['No', 'Nama Barang', 'Kode', 'Saldo Awal'];

        // Masuk dulu
        foreach ($this->days as $d) $h[] = (string) $d;
        $h[] = 'Total Masuk';

        // Baru Keluar
        foreach ($this->days as $d) $h[] = (string) $d;
        $h[] = 'Total Keluar';

        $h[] = 'Saldo Akhir';
        $h[] = 'Satuan';

        return $h;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $rows = [];

        foreach ($this->data['rows'] as $i => $r) {
            $line = [
                $i + 1,
                $r['name'],
                $r['code'],
                (int) ($r['opening'] ?? 0),
            ];

            // Masuk dulu
            foreach ($this->days as $d) $line[] = (int) ($r['in_daily'][$d]  ?? 0);
            $line[] = (int) ($r['in_total']  ?? 0);

            // Baru Keluar
            foreach ($this->days as $d) $line[] = (int) ($r['out_daily'][$d] ?? 0);
            $line[] = (int) ($r['out_total'] ?? 0);

            $line[] = (int) ($r['closing'] ?? 0);
            $line[] = $r['unit'];

            $rows[] = $line;
        }

        return collect($rows);
    }

    public function title(): string
    {
        return mb_substr($this->categoryName ?: 'Semua Barang', 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $days  = count($this->days);

                // ── Hitung posisi kolom (1-based index) ──────────────────────
                // A=1  No
                // B=2  Nama Barang
                // C=3  Kode
                // D=4  Saldo Awal
                // E..  hari masuk  (days kolom)
                // +1   Total Masuk
                // +1.. hari keluar (days kolom)
                // +1   Total Keluar
                // +1   Saldo Akhir
                // +1   Satuan

                $colSaldoAwal    = 4;                          // D
                $colInStart      = 5;                          // E
                $colInEnd        = $colInStart + $days - 1;   // E + days - 1
                $colTotalMasuk   = $colInEnd + 1;
                $colOutStart     = $colTotalMasuk + 1;
                $colOutEnd       = $colOutStart + $days - 1;
                $colTotalKeluar  = $colOutEnd + 1;
                $colSaldoAkhir   = $colTotalKeluar + 1;
                $colSatuan       = $colSaldoAkhir + 1;

                $lastCol         = $colSatuan;
                $lastColLetter   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastCol);

                // Helper: konversi index → letter
                $col = fn(int $i) => \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                // ── Tinggi baris header ───────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(36);

                // ── Styling: kolom tetap (No, Nama, Kode, Saldo Awal) ─────────
                $sheet->getStyle("A1:{$col($colSaldoAwal)}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E4057'], // biru tua gelap
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Styling: kolom tanggal MASUK ──────────────────────────────
                $sheet->getStyle("{$col($colInStart)}1:{$col($colInEnd)}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '27AE60'], // hijau → masuk
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Styling: Total Masuk ───────────────────────────────────────
                $sheet->getStyle("{$col($colTotalMasuk)}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E8449'], // hijau tua
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Styling: kolom tanggal KELUAR ─────────────────────────────
                $sheet->getStyle("{$col($colOutStart)}1:{$col($colOutEnd)}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E74C3C'], // merah → keluar
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Styling: Total Keluar ─────────────────────────────────────
                $sheet->getStyle("{$col($colTotalKeluar)}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'C0392B'], // merah tua
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Styling: Saldo Akhir + Satuan ────────────────────────────
                $sheet->getStyle("{$col($colSaldoAkhir)}1:{$lastColLetter}1")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E4057'], // sama dgn kolom tetap
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                    ],
                ]);

                // ── Freeze pane supaya header + 4 kolom kiri tetap ───────────
                $sheet->freezePane("{$col($colInStart)}2");

                // ── Border seluruh data ───────────────────────────────────────
                $totalRows = count($this->data['rows']) + 1; // +1 header
                if ($totalRows > 1) {
                    $sheet->getStyle("A1:{$lastColLetter}{$totalRows}")->applyFromArray([
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']],
                        ],
                    ]);
                }

                // ── Zebra striping pada baris data ────────────────────────────
                for ($row = 2; $row <= $totalRows; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2'],
                            ],
                        ]);
                    }
                }

                // ── Lebar kolom No (A) diperkecil ────────────────────────────
                $sheet->getColumnDimension('A')->setAutoSize(false);
                $sheet->getColumnDimension('A')->setWidth(6);

                // ── Center + Middle align semua data (baris 2 dst) ───────────────
                if ($totalRows > 1) {
                    $sheet->getStyle("A2:{$lastColLetter}{$totalRows}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    // Khusus kolom Nama Barang (B) → tetap left align, tapi middle
                    $sheet->getStyle("B2:B{$totalRows}")->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                // ── Tinggi baris data ─────────────────────────────────────────────
                for ($row = 2; $row <= $totalRows; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }
            },
        ];
    }
}
