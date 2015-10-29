<?php
namespace SPHERE\Application\Education\Lesson\Term;

use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Term
 *
 * @package SPHERE\Application\Education\Lesson\Term
 */
class Term implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Schuljahr'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Year', __NAMESPACE__.'\Frontend::frontendCreateYear'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Create/Period', __NAMESPACE__.'\Frontend::frontendCreatePeriod'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Link/Period', __NAMESPACE__.'\Frontend::frontendLinkPeriod'
        ));
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'Schuljahr');

        $Stage->addButton(new Standard('Schuljahr hinzufügen', __NAMESPACE__.'\Create\Year'));
        $Stage->addButton(new Standard('Zeitraum hinzufügen', __NAMESPACE__.'\Create\Period'));

        $tblYearAll = Term::useService()->getYearAll();
        $Year = array();
        if ($tblYearAll) {
            array_walk($tblYearAll, function (TblYear $tblYear) use (&$Year) {

                $tblPeriodAll = $tblYear->getTblPeriodAll();
                if ($tblPeriodAll) {
                    array_walk($tblPeriodAll, function (TblPeriod &$tblPeriod) {

                        $tblPeriod = $tblPeriod->getName().' '.$tblPeriod->getDescription()
                            .'<br/>'.$tblPeriod->getFromDate().' - '.$tblPeriod->getToDate();
                    });
                } else {
                    $tblPeriodAll = array();
                }
                array_push($Year, array(
                    'Schuljahr' => $tblYear->getName().' '.$tblYear->getDescription(),
                    'Zeiträume' => new Panel(
                        ( empty( $tblPeriodAll ) ? 'Keine Zeiträume hinterlegt' : count($tblPeriodAll).' Zeiträume' ),
                        $tblPeriodAll,
                        ( empty( $tblPeriodAll ) ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT )),
                    'Optionen'  =>
                        new Standard('', __NAMESPACE__.'\Edit\Year', new Pencil(),
                            array('Id' => $tblYear->getId()), 'Bearbeiten'
                        ).
                        ( empty( $tblPeriodAll )
                            ? new Standard('', __NAMESPACE__.'\Destroy\Year', new Remove(),
                                array('Id' => $tblYear->getId()), 'Löschen'
                            ) : ''
                        )
                ));
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData(
                                $Year, null, array(
                                    'Schuljahr' => 'Schuljahr',
                                    'Zeiträume' => 'Zeiträume',
                                    'Optionen'  => 'Optionen'
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Education', 'Lesson', 'Term', null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }
}
