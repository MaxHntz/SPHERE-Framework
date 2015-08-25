<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Person
 *
 * @package SPHERE\Application\People\Person
 */
class Person implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Person' ),
                new Link\Icon( new \SPHERE\Common\Frontend\Icon\Repository\Person() )
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendPerson'
        ) );

        // Contact: Address [Create]
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Address/Create', 'SPHERE\Application\Contact\Address\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault( 'Street', null )
            ->setParameterDefault( 'City', null )
            ->setParameterDefault( 'State', null )
            ->setParameterDefault( 'Type', null )
        );
        // Contact: Mail [Create]
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Create', 'SPHERE\Application\Contact\Mail\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault( 'Address', null )
            ->setParameterDefault( 'Type', null )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Mail/Edit', 'SPHERE\Application\Contact\Mail\Frontend::frontendUpdateToPerson'
        )
            ->setParameterDefault( 'Address', null )
            ->setParameterDefault( 'Type', null )
        );
        // Contact: Phone [Create]
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Phone/Create', 'SPHERE\Application\Contact\Phone\Frontend::frontendCreateToPerson'
        )
            ->setParameterDefault( 'Number', null )
            ->setParameterDefault( 'Type', null )
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier( 'People', 'Person', null, null, Consumer::useService()->getConsumerBySession() ),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }


}
