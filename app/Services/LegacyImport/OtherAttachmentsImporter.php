<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

final class OtherAttachmentsImporter extends AttachmentsImporter
{
    public static function key(): string
    {
        return 'other_attachments';
    }

    protected function legacyTable(): string
    {
        return 'other_attachment';
    }
}
