<?php

namespace App\Domain\Organization\Enums;

enum OrganizationTemplate: string
{
    case School = 'school';
    case Campus = 'campus';
    case Office = 'office';
    case Community = 'community';

    public function label(): string
    {
        return match ($this) {
            self::School => 'Sekolah',
            self::Campus => 'Kampus',
            self::Office => 'Kantor',
            self::Community => 'Komunitas / Rental',
        };
    }

    /**
     * Terminologi bawaan per jenis organisasi. Bisa ditimpa lewat organizations.settings['terms'].
     *
     * @return array{unit: string, unit_plural: string, borrower_id_label: string}
     */
    public function defaultTerms(): array
    {
        return match ($this) {
            self::School => ['unit' => 'Jurusan', 'unit_plural' => 'Jurusan', 'borrower_id_label' => 'NIS / NIP'],
            self::Campus => ['unit' => 'Fakultas', 'unit_plural' => 'Fakultas', 'borrower_id_label' => 'NIM / NIDN'],
            self::Office => ['unit' => 'Divisi', 'unit_plural' => 'Divisi', 'borrower_id_label' => 'ID Karyawan'],
            self::Community => ['unit' => 'Unit', 'unit_plural' => 'Unit', 'borrower_id_label' => 'NIK / No. HP'],
        };
    }
}
