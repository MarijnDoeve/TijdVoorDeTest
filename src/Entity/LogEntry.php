<?php

declare(strict_types=1);

namespace Tvdt\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 * Custom LogEntry that stores change data as JSON (instead of serialized `array`)
 * so it works with the PostgreSQL-only DBAL setup in this project.
 *
 * @extends AbstractLogEntry<object>
 */
#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
#[ORM\Index(name: 'log_class_lookup_idx', columns: ['object_class'])]
#[ORM\Index(name: 'log_date_lookup_idx', columns: ['logged_at'])]
#[ORM\Index(name: 'log_user_lookup_idx', columns: ['username'])]
#[ORM\Index(name: 'log_version_lookup_idx', columns: ['object_id', 'object_class', 'version'])]
#[ORM\Table(name: 'ext_log_entries')]
class LogEntry extends AbstractLogEntry
{
    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[\Override]
    protected $data;
}
