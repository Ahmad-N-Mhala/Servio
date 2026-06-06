<?php

namespace App\Mail;

use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $ingredient;

    public function __construct(Ingredient $ingredient)
    {
        $this->ingredient = $ingredient;
    }

    public function build()
    {
        $name = $this->ingredient->name['en'] ?? ($this->ingredient->name['ar'] ?? 'Unknown Item');

        return $this->subject('Low Stock Warning: '.$name)
            ->view('emails.low_stock_warning');
    }
}
