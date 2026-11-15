<?php

namespace App\Observers;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use DomainException;

class JournalEntryObserver
{
    /**
     * Handle the JournalEntry "creating" event.
     */
    public function creating(JournalEntry $entry): void
    {
        $date = $entry->entry_date ? $entry->entry_date->format('Y-m-d') : now()->toDateString();
        if (AccountingPeriod::isDateLocked($date)) {
            throw new DomainException("Operasi Ditolak: Tanggal transaksi [{$date}] berada pada periode akuntansi yang telah DIKUNCI / CLOSED.");
        }
    }

    /**
     * Handle the JournalEntry "updating" event.
     */
    public function updating(JournalEntry $entry): void
    {
        $originalDate = $entry->getOriginal('entry_date');
        if ($originalDate) {
            $formattedOriginal = is_string($originalDate) ? substr($originalDate, 0, 10) : $originalDate->format('Y-m-d');
            if (AccountingPeriod::isDateLocked($formattedOriginal)) {
                throw new DomainException("Operasi Ditolak: Jurnal pada tanggal asli [{$formattedOriginal}] telah berada pada periode buku yang DIKUNCI.");
            }
        }

        $newDate = $entry->entry_date ? $entry->entry_date->format('Y-m-d') : now()->toDateString();
        if (AccountingPeriod::isDateLocked($newDate)) {
            throw new DomainException("Operasi Ditolak: Perubahan tanggal jurnal ke [{$newDate}] berada pada periode akuntansi yang telah DIKUNCI.");
        }
    }

    /**
     * Handle the JournalEntry "deleting" event.
     */
    public function deleting(JournalEntry $entry): void
    {
        $date = $entry->entry_date ? $entry->entry_date->format('Y-m-d') : now()->toDateString();
        if (AccountingPeriod::isDateLocked($date)) {
            throw new DomainException("Operasi Ditolak: Penghapusan jurnal tanggal [{$date}] dilarang karena periode buku telah DIKUNCI.");
        }
    }
}
