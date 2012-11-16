<?php
    /**
     * IRC Bot
     *
     * LICENSE: This source file is subject to Creative Commons Attribution
     * 3.0 License that is available through the world-wide-web at the following URI:
     * http://creativecommons.org/licenses/by/3.0/.  Basically you are free to adapt
     * and use this script commercially/non-commercially. My only requirement is that
     * you keep this header as an attribution to my work. Enjoy!
     *
     * @license http://creativecommons.org/licenses/by/3.0/
     *
     * @package IRCBot
     * @author Super3 <admin@wildphp.org>
     */

    // Configure PHP
    set_time_limit( 0 );
    ini_set( 'display_errors', 'on' );

    // Make autoload working
    require 'IRC-Bot/Classes/Autoloader.php';
    
    spl_autoload_register(function ($class) {

        $filename = str_replace( '\\', '/', $class ) . '.php';

        if (file_exists($filename)) {
            return require $filename;
        }

        Autoloader::load($class);
    });

    // Create the bot.
    $bot = new Library\IRC\Bot();

    // Configure the bot.
    $bot->setServer( 'irc.gendoc.com.br' );
    $bot->setPort( 6667 );
    $bot->setChannel( array('#gendoc') );
    $bot->setName( 'Jarvis Beta' );
    $bot->setNick( 'JarvisBeta' );
    $bot->setMaxReconnects( 1 );
    $bot->setLogFile( '/home/porkaria/codigo/log/jarvis/log' );

    // Add commands to the bot.
    $bot->addCommand( new Command\Say );
    $bot->addCommand( new Command\Poke );
    $bot->addCommand( new Command\Join );
    $bot->addCommand( new Command\Part );
    $bot->addCommand( new Command\Timeout );
    $bot->addCommand( new Command\Quit );
    $bot->addCommand( new Command\Restart );

    $service = new Service\Gendoc\ReadLogService('/home/porkaria/codigo/log/ionews_sergipe/error_log');
    $service->setChannels = array('#gendoc');

    $bot->addService($service);


    // Connect to the server.
    $bot->connectToServer();

    // Nothing more possible, the bot runs until script ends.
?>