<?php
namespace SPHERE\Application\Education\Graduation;

use SPHERE\Application\Education\Graduation\Certificate\Certificate;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Education\Graduation\Gradebook\Gradebook;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Graduation
 *
 * @package SPHERE\Application\Education\Graduation
 */
class Graduation implements IApplicationInterface
{

    public static function registerApplication()
    {

        Gradebook::registerModule();
        Evaluation::registerModule();
        Certificate::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Zensuren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Zensuren', 'Überblick');
        $Stage->setContent(
            '<br>'
            . new Table(new TableHead(), new TableBody(array(
                new TableRow(array(
                    new TableColumn(
                        new Bold('Zensuren-Typ')
                    ),
                    new TableColumn(
                        'Verwaltung der Zensuren-Typen (Kopfnoten, Leistungsüberprüfung).'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Berechnungsvorschrift')
                    ),
                    new TableColumn(
                        'Verwaltung der Berechnungsvorschriften für die automatische Durchschnittsberechnung der Zensuren.'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Notenbuch')
                    ),
                    new TableColumn(
                        'Anzeige der Notenbücher, wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Notenbuch (Leitung)')
                    ),
                    new TableColumn(
                        'Anzeige aller Notenbücher.'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Notenübersicht')
                    ),
                    new TableColumn(
                        'Anzeige der Zensuren für die Schüler und Eltern. <br>
                        Der angemeldete Schüler sieht nur seine eigenen Zensuren. <br>
                        Der angemeldete Sorgeberechtigte sieht nur die Zensuren seiner Schützlinge. <br>'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Leistungsüberprüfung')
                    ),
                    new TableColumn(
                        'Verwaltung der Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten)
                        , wo der angemeldete Lehrer als Fachlehrer oder Klassenlehrer hinterlegt ist.'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Leistungsüberprüfung (Leitung)')
                    ),
                    new TableColumn(
                        'Verwaltung aller Leistungsüberprüfungen (inklusive Kopfnoten und Stichtagsnoten).'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Notenaufträge (Klassenlehrer)')
                    ),
                    new TableColumn(
                        'Anzeige der Kopfnoten- und Stichtagsnotenaufträge (inklusive vergebener Zensuren),
                         wo der angemeldete Lehrer als Klassenlehrer hinterlegt ist.'
                    ),
                )),
                new TableRow(array(
                    new TableColumn(
                        new Bold('Notenaufträge')
                    ),
                    new TableColumn(
                        'Verwaltung aller Kopfnoten- und Stichtagsnotenaufträge (inklusive der Anzeige der vergebener Zensuren).'
                    ),
                )),
            )))

        );

        return $Stage;
    }
}
