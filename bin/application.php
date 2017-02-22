<?php
use HylianShield\NumberGenerator\NumberGenerator;
use Symfony\Component\Console\Application;
use ZeroConfig\D\Command\DiceRollCommand;
use ZeroConfig\D\Command\FateRollCommand;
use ZeroConfig\D\DieFactory;
use ZeroConfig\D\Interpreter;
use ZeroConfig\D\RollFactory;

require_once __DIR__ . '/autoload.php';

$name = <<<LOGO
______       ______ _____ _      _     
|  _  \      | ___ \  _  | |    | |    
| | | |______| |_/ / | | | |    | |    
| | | |______|    /| | | | |    | |    
| |/ /       | |\ \\ \_/ / |____| |____
|___/        \_| \_|\___/\_____/\_____/
LOGO;

$interpreter = new Interpreter(
    new DieFactory(
        new RollFactory(),
        new NumberGenerator()
    )
);

$application = new Application($name);
$application->add(new DiceRollCommand($interpreter));
$application->add(new FateRollCommand($interpreter));

return $application;
