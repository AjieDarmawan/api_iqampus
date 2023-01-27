<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $nama;
    public $kampus;
    public $item;
    public $situs;
    public $email;
    public $password;
    public $biaya;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nama,$kampus,$situs,$email,$password)
    {
        $this->nama = $nama;
        $this->kampus = $kampus;
        // $this->item = $item;
        $this->situs = $situs;
        $this->email = $email;
        $this->password = $password;        
        // $this->biaya = $biaya;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email',[
            'nama'=>$this->nama,
            'kampus'=>$this->kampus,
            // 'item'=>$this->item,
            'situs'=>$this->situs,
            'email'=>$this->email,
            'password'=>$this->password,
            // 'biaya'=>$this->biaya
        ]);
    }
}
