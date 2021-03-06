<?php
namespace Codeages\Biz\Framework\Context;

use Symfony\Component\Console\Command\Command;
use Codeages\Biz\Framework\Context\Biz;

class AbstractCommand extends Command
{
    protected $biz;

    public function __construct(Biz $biz, $name = null)
    {
        $this->biz = $biz;
        parent::__construct($name);
    }
}