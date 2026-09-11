<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FeedbackMultiSheetExport implements WithMultipleSheets
{
    public function __construct(
        private array $ratingsData,
        private array $suggestionsData
    ) {}

    public function sheets(): array
    {
        return [
            new GenericExport($this->ratingsData, 'Rekap Rating Katering'),
            new GenericExport($this->suggestionsData, 'Saran & Masukan Karyawan'),
        ];
    }
}
