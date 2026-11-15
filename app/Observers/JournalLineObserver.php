<?php

namespace App\Observers;

use App\Models\AccountingPeriod;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use DomainException;

class JournalLineObserver
{
    /**
     * Pastikan entry induk tidak berada di periode buku terkunci.
     */
    protected function checkParentLock(JournalLine $line): void
    {
        $entryId = $line->entry_id ?? $line->journal_entry_id ?? $line->getOriginal('entry_id') ?? $line->getOriginal('journal_entry_id');
        if (!$entryId) {
            return;
        }

        $entry = $line->relationLoaded('entry')
            ? $line->entry
            : ($line->relationLoaded('journalEntry')
                ? $line->journalEntry
                : JournalEntry::find($entryId));

        if ($entry && $entry->entry_date) {
            $date = is_string($entry->entry_date) ? substr($entry->entry_date, 0, 10) : $entry->entry_date->format('Y-m-d');
            if (AccountingPeriod::isDateLocked($date)) {
                throw new DomainException("Operasi Ditolak: Perubahan item baris jurnal dilarang karena periode buku tanggal [{$date}] telah DIKUNCI.");
            }
        }
    }

    public function creating(JournalLine $line): void
    {
        $this->checkParentLock($line);
    }

    public function updating(JournalLine $line): void
    {
        $this->checkParentLock($line);
    }

    public function deleting(JournalLine $line): void
    {
        $this->checkParentLock($line);
    }
}
