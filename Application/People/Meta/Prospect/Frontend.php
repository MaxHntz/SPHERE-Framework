<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array())
    {

        $Stage = new Stage();

        $Stage->setMessage(
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta'] )) {
                /** @var TblProspect $tblProspect */
                $tblProspect = Prospect::useService()->getProspectByPerson($tblPerson);
                if ($tblProspect) {
                    $Global->POST['Meta']['Remark'] = $tblProspect->getRemark();
                    /** @var TblProspectAppointment $tblProspectAppointment */
                    $tblProspectAppointment = $tblProspect->getTblProspectAppointment();
                    if ($tblProspectAppointment) {
                        $Global->POST['Meta']['Appointment']['ReservationDate'] = $tblProspectAppointment->getReservationDate();
                        $Global->POST['Meta']['Appointment']['InterviewDate'] = $tblProspectAppointment->getInterviewDate();
                        $Global->POST['Meta']['Appointment']['TrialDate'] = $tblProspectAppointment->getTrialDate();
                    }
                    /** @var TblProspectReservation $tblProspectReservation */
                    $tblProspectReservation = $tblProspect->getTblProspectReservation();
                    if ($tblProspectReservation) {
                        $Global->POST['Meta']['Reservation']['Year'] = $tblProspectReservation->getReservationYear();
                        $Global->POST['Meta']['Reservation']['Division'] = $tblProspectReservation->getReservationDivision();
                    }
                    $Global->savePost();
                }
            }
        }

        $Stage->setContent(
            Prospect::useService()->createMeta(
                (new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(array(
                                new Panel('Termine', array(
                                    new DatePicker('Meta[Appointment][ReservationDate]', 'Eingangsdatum',
                                        'Eingangsdatum',
                                        new Calendar()
                                    ),
                                    new DatePicker('Meta[Appointment][InterviewDate]', 'Aufnahmegespräch',
                                        'Aufnahmegespräch',
                                        new Calendar()
                                    ),
                                    new DatePicker('Meta[Appointment][TrialDate]', 'Schnuppertag', 'Schnuppertag',
                                        new Calendar()
                                    ),
                                ), Panel::PANEL_TYPE_INFO)
                            ), 6),
                            new FormColumn(array(
                                new Panel('Voranmeldung für', array(
                                    new TextField('Meta[Reservation][Year]', 'Schuljahr', 'Schuljahr'),
                                    new TextField('Meta[Reservation][Division]', 'Klassenstufe', 'Klassenstufe'),
                                ), Panel::PANEL_TYPE_INFO)
                            ), 6),
                        )),
                        new FormRow(array(
                            new FormColumn(array(
                                new Panel('Sonstiges', array(
                                    new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                                ), Panel::PANEL_TYPE_INFO)
                            )),
                        )),
                    )),
                ), new Primary('Informationen speichern')
                ))->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'), $tblPerson, $Meta)
        );

        return $Stage;
    }
}
