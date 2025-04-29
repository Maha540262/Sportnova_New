<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EquipementTypeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('status_color', [$this, 'getStatusColor']),
            new TwigFilter('status_format', [$this, 'formatStatus']),
        ];
    }

    public function getStatusColor($status)
    {
        $status = strtolower($status);
        if (strpos($status, 'neuf') !== false) return 'success';
        if (strpos($status, 'bon état') !== false) return 'info';
        if (strpos($status, 'usage') !== false) return 'warning';
        if (strpos($status, 'endommagé') !== false) return 'danger';
        return 'secondary';
    }

    public function formatStatus($status)
    {
        $status = strtolower($status);
        if ($status === 'neuf') return 'Neuf';
        if ($status === 'bon état') return 'Bon état';
        if ($status === 'usage' || $status === 'used') return 'Usage';
        if ($status === 'new') return 'Neuf';
        return ucfirst($status);
    }
}
