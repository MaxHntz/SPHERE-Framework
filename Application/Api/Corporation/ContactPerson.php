<?php
namespace SPHERE\Application\Api\Corporation;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Common\Frontend\Ajax\Emitter\ApiEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class ContactPerson
 *
 * @package SPHERE\Application\Api\Corporation
 */
class ContactPerson implements IApiInterface
{

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Similar', __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {

        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('ajaxLayoutSimilarPerson');
        $Dispatcher->registerMethod('ajaxFormCreateContactPerson');

        return $Dispatcher->callMethod($MethodName);
    }


    public function ajaxLayoutSimilarPerson( $TblSalutation_Id = 1, $TblPerson_FirstName, $TblPerson_LastName, $Reload = null )
    {
        $Search = new Pile();
        $Search->addPile( Person::useService(), new ViewPerson() );

        $FirstName = explode( ' ', $TblPerson_FirstName );
        $LastName = explode( ' ', $TblPerson_LastName );

        $Result = $Search->searchPile(array(
            array(
//                ViewPerson::TBL_SALUTATION_ID => $TblSalutation_Id ? array($TblSalutation_Id) : array(''),
//                ViewPerson::TBL_PERSON_FIRST_NAME => $FirstName,
//                ViewPerson::TBL_PERSON_LAST_NAME => $LastName,
                ViewPerson::TBL_SALUTATION_ID => array($TblSalutation_Id),
                ViewPerson::TBL_PERSON_FIRST_NAME => array('g'),
                ViewPerson::TBL_PERSON_LAST_NAME => array(''),
            )
        ));

        $Result = array_slice( $Result, 0, 100 );

        $R = new InlineReceiver();

        $Table = array();
        foreach( $Result as $Row ) {

            $P = new Pipeline();
            $P->addEmitter( $E = new ApiEmitter( new Route(__NAMESPACE__.'/Similar'), $R ) );
            $E->setGetPayload(array( 'MethodName' => 'ajaxFormCreateContactPerson' ));
            $E->setPostPayload( array(
                ViewPerson::TBL_SALUTATION_ID => 2,
                ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
                ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
                'Reload' => $Reload
            ));

            $ViewPerson = $Row[0]->__toArray();

            $P->setLoadingMessage('Datensatz ('.$ViewPerson[ViewPerson::TBL_PERSON_FIRST_NAME].') wird verbunden..');
            $P->setSuccessMessage('Datensatz ('.$ViewPerson[ViewPerson::TBL_PERSON_FIRST_NAME].') wurde verbunden');

            $ViewPerson['DTOption'] = (new Standard('Ansprechpartner anlegen','#'))->ajaxPipelineOnClick( $P );
            $Table[] = $ViewPerson;
        }

        $P = new Pipeline();
        $P->addEmitter( $E = new ApiEmitter( new Route(__NAMESPACE__.'/Similar'), $R ) );
        $E->setGetPayload(array( 'MethodName' => 'ajaxFormCreateContactPerson' ));
        $E->setPostPayload( array(
            ViewPerson::TBL_SALUTATION_ID => 3,
            ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
            ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
            'Reload' => $Reload
        ));
        $P->setLoadingMessage('Neuer Datensatz wird erzeugt..');
        $P->setSuccessMessage('Neuer Datensatz wurde erzeugt');

        return new TableData($Table, null, array(
            ViewPerson::TBL_SALUTATION_SALUTATION => 'Anrede',
            ViewPerson::TBL_PERSON_FIRST_NAME => 'Vorname',
            ViewPerson::TBL_PERSON_LAST_NAME => 'Nachname',
                'DTOption' => ''
        ),array(
            "columnDefs" => array(
                array( "searchable" => false, "targets" => -1 ),
                array( "type" => "natural", "targets" => '_all' )
            )
            )
            ).


            (new Standard('Ansprechpartner anlegen','#'))->ajaxPipelineOnClick( $P ).$R;
    }

    public function ajaxFormCreateContactPerson( $TblSalutation_Id, $TblPerson_FirstName, $TblPerson_LastName, $Reload )
    {

        $P = new Pipeline();
        $P->setLoadingMessage('Daten werde neu geladen...');
        $P->setSuccessMessage('Daten wurden neu geladen');
        $P->addEmitter( $E = new ApiEmitter( new Route(__NAMESPACE__.'/Similar'), $R = new ModalReceiver() ) );
        $R->setIdentifier( $Reload );
        $E->setGetPayload(array( 'MethodName' => 'ajaxLayoutSimilarPerson' ));
        $E->setPostPayload( array(
            ViewPerson::TBL_SALUTATION_ID => $TblSalutation_Id,
            ViewPerson::TBL_PERSON_FIRST_NAME => $TblPerson_FirstName,
            ViewPerson::TBL_PERSON_LAST_NAME => $TblPerson_LastName,
            'Reload' => $Reload
        ));

        sleep(1);
        return new Success('Done '.time()).$P;
    }
}