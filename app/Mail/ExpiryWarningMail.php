<?php

namespace App\Mail;

use App\Models\IngredientBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $batch;
    public $daysRemaining;

    public function __construct(IngredientBatch $batch, $daysRemaining)
    {
        $this->batch = $batch;
        $this->daysRemaining = $daysRemaining;
    }

    public function build()
    {
        $ingredientName = $this->batch->ingredient->name['en'] ?? ($this->batch->ingredient->name['ar'] ?? 'Unknown Item');

        return $this->subject('Inventory Expiry Warning: ' . $ingredientName)
            ->view('emails.expiry_warning');
    }
}
