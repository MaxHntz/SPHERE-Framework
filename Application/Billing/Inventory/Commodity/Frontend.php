<?php

namespace SPHERE\Application\Billing\Inventory\Commodity;

use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodityItem;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Commodity
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $Commodity
     *
     * @return Stage
     */
    public function frontendStatus($Commodity = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistungen');
        $Stage->setDescription('Übersicht');
//        $Stage->setMessage('Zeigt die verfügbaren Leistungen an. <br />
//                            Leistungen sind Zusammenfassungen aller Artikel,
//                            die unter einem Punkt für den Debitor abgerechnet werden. <br />
//                            Beispielsweise: Schulgeld, Hortgeld, Klassenfahrt usw.');
//        $Stage->addButton(
//            new Standard('Leistung anlegen', '/Billing/Inventory/Commodity/Create', new Plus())
//        );

        $tblCommodityAll = Commodity::useService()->getCommodityAll();

        $TableContent = array();
        if (!empty( $tblCommodityAll )) {
            array_walk($tblCommodityAll, function (TblCommodity $tblCommodity) use (&$TableContent) {

                $Item['Name'] = $tblCommodity->getName();
                $Item['Description'] = $tblCommodity->getDescription();
                $Item['ItemCount'] = Commodity::useService()->countItemAllByCommodity($tblCommodity);
                $Item['SumPriceItem'] = Commodity::useService()->sumPriceItemAllByCommodity($tblCommodity);
                $Item['Option'] = (new Standard('Bearbeiten', '/Billing/Inventory/Commodity/Change',
                        new Pencil(), array(
                            'Id' => $tblCommodity->getId()
                        )))->__toString().
                    (new Standard('Artikel auswählen', '/Billing/Inventory/Commodity/Item/Select',
                        new Listing(), array(
                            'Id' => $tblCommodity->getId()
                        )))->__toString();
//                    .(new Standard('Löschen', '/Billing/Inventory/Commodity/Destroy',
//                        new Remove(), array(
//                            'Id' => $tblCommodity->getId()
//                        )))->__toString();
                array_push($TableContent, $Item);
            });
        }
        $Form = $this->formCommodity()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name'         => 'Name',
                                    'Description'  => 'Beschreibung',
                                    'ItemCount'    => 'Artikelanzahl',
                                    'SumPriceItem' => 'Gesamtpreis',
                                    'Option'       => ''
                                )
                            )
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Commodity::useService()->createCommodity($Form, $Commodity)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );

        return $Stage;
    }

//    /**
//     * @param $Commodity
//     *
//     * @return Stage
//     */
//    public function frontendCreate($Commodity)
//    {
//
//        $Stage = new Stage();
//        $Stage->setTitle('Leistung');
//        $Stage->setDescription('Hinzufügen');
//        $Stage->setMessage(
//            '<b>Hinweis:</b> <br>
//            Bei einer Einzelleistung wird für jede Person der gesamten Betrag berechnet. <br>
//            Hingegen bei einer Sammelleisung bezahlt jede Person einen Teil des gesamten Betrags, abhängig von der
//            Personenanzahl. <br>
//            (z.B.: für Klassenfahrten)
//        ');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Commodity',
//            new ChevronLeft()
//        ));
//
//        $Form = $this->formCommodity()
//            ->appendFormButton(new Primary('Hinzufügen'))
//            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');
//
//        $Stage->setContent(Commodity::useService()->createCommodity($Form, $Commodity));
//
//        return $Stage;
//    }

    /**
     * @return Form
     */
    public function formCommodity()
    {

        return new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Leistung', array(new TextField('Commodity[Name]', 'Name', 'Name', new Conversation()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Sonstiges',
                            new TextArea('Commodity[Description]', 'Beschreibung', 'Beschreibung', new Conversation()),
                            Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            ))
        ));
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendDestroy($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Entfernen');

        $tblCommodity = Commodity::useService()->getCommodityById($Id);
        $Stage->setContent(Commodity::useService()->destroyCommodity($tblCommodity));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Commodity
     *
     * @return Stage
     */
    public function frontendChange($Id, $Commodity)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistungen');
        $Stage->setDescription('Bearbeiten');
//        $Stage->setMessage(
//            '<b>Hinweis:</b> <br>
//            Bei einer Einzelleistung wird für jede Person der gesamten Betrag berechnet. <br>
//            Hingegen bei einer Sammelleisung bezahlt jede Person einen Teil des gesamten Betrags, abhängig von der
//            Personenanzahl. <br>
//            (z.B.: für Klassenfahrten)
//        ');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Commodity',
            new ChevronLeft()
        ));

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblCommodity = Commodity::useService()->getCommodityById($Id);
            if (empty( $tblCommodity )) {
                $Stage->setContent(new Warning('Die Leistung konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['Commodity'] )) {
                    $Global->POST['Commodity']['Name'] = $tblCommodity->getName();
                    $Global->POST['Commodity']['Description'] = $tblCommodity->getDescription();
                    $Global->savePost();
                }

                $PanelValue = array();
                $PanelValue[0] = $tblCommodity->getName();
                $PanelValue[1] = $tblCommodity->getDescription();
                $PanelContent = new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $PanelValue[0], Panel::PANEL_TYPE_INFO)
                                , 6),
                            new LayoutColumn(
                                new Panel('Beschreibung', $PanelValue[1], Panel::PANEL_TYPE_INFO)
                            , 6),
                        ))
                    )
                );

                $Form = $this->formCommodity()
                    ->appendFormButton(new Primary('Speichern', new Save()))
                    ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    $PanelContent
                                )
                            )
                        )
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(new Well(
                                    Commodity::useService()->changeCommodity($Form, $tblCommodity, $Commodity)
                                ))
                            ), new Title(new Pencil().' Bearbeiten')
                        )
                    )
                );
            }
        }

        return $Stage;
    }

    /**
     * @param $tblCommodityId
     * @param $tblItemId
     * @param $Item
     *
     * @return Stage
     */
    public function frontendItemAdd($tblCommodityId, $tblItemId, $Item)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Artikel Hinzufügen');
        $tblCommodity = Commodity::useService()->getCommodityById($tblCommodityId);
        $tblItem = Item::useService()->getItemById($tblItemId);

        if (!empty( $tblCommodityId ) && !empty( $tblItemId )) {
            $Stage->setContent(Commodity::useService()->addItemToCommodity($tblCommodity, $tblItem, $Item));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendItemAccountSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('FIBU-Konten auswählen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item',
            new ChevronLeft()
        ));

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblItem = Item::useService()->getItemById($Id);
            if (empty( $tblItem )) {
                $Stage->setContent(new Warning('Der Artikel konnte nicht abgerufen werden'));
            } else {
                $tblItemAccountByItem = Item::useService()->getItemAccountAllByItem($tblItem);
                $tblAccountByItem = Commodity::useService()->getAccountAllByItem($tblItem);
                $tblAccountAllByActiveState = Account::useService()->getAccountAllByActiveState();

                if (!empty( $tblAccountAllByActiveState )) {
                    $tblAccountAllByActiveState = array_udiff($tblAccountAllByActiveState, $tblAccountByItem,
                        function (TblAccount $ObjectA, TblAccount $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if (!empty( $tblItemAccountByItem )) {
                    array_walk($tblItemAccountByItem, function (TblItemAccount $tblItemAccountByItem) {

                        $tblItemAccountByItem->Number = $tblItemAccountByItem->getServiceBillingAccount()->getNumber();
                        $tblItemAccountByItem->Description = $tblItemAccountByItem->getServiceBillingAccount()->getDescription();
                        $tblItemAccountByItem->Option =
                            new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen', '/Billing/Inventory/Commodity/Item/Account/Remove',
                                new Minus(), array(
                                    'Id' => $tblItemAccountByItem->getId()
                                ));
                    });
                }

                if (!empty( $tblAccountAllByActiveState )) {
                    /** @noinspection PhpUnusedParameterInspection */
                    array_walk($tblAccountAllByActiveState,
                        function (TblAccount $tblAccountAllByActiveState, $Index, TblItem $tblItem) {

                            $tblAccountAllByActiveState->Option =
                                new \SPHERE\Common\Frontend\Link\Repository\Primary('Hinzufügen', '/Billing/Inventory/Commodity/Item/Account/Add',
                                    new Plus(), array(
                                        'tblAccountId' => $tblAccountAllByActiveState->getId(),
                                        'tblItemId'    => $tblItem->getId()
                                    ));
                        }, $tblItem);
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Name', $tblItem->getName(), Panel::PANEL_TYPE_SUCCESS), 4
                                ),
                                new LayoutColumn(
                                    new Panel('Beschreibung', $tblItem->getDescription(), Panel::PANEL_TYPE_SUCCESS), 8
                                )
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                        new TableData($tblItemAccountByItem, null,
                                            array(
                                                'Number'      => 'Nummer',
                                                'Description' => 'Beschreibung',
                                                'Option'      => ''
                                            )
                                        )
                                    )
                                )
                            )),
                        ), new Title('zugewiesene FIBU-Konten')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                        new TableData($tblAccountAllByActiveState, null,
                                            array(
                                                'Number'      => 'Nummer',
                                                'Description' => 'Beschreibung',
                                                'Option'      => ''
                                            )
                                        )
                                    )
                                )
                            )),
                        ), new Title('mögliche FIBU-Konten'))
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendItemSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Artikel auswählen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Commodity',
            new ChevronLeft()
        ));

        if (empty( $Id )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblCommodity = Commodity::useService()->getCommodityById($Id);
            if (empty( $tblCommodity )) {
                $Stage->setContent(new Warning('Die Leistung konnte nicht abgerufen werden'));
            } else {
                $tblCommodityItem = Commodity::useService()->getCommodityItemAllByCommodity($tblCommodity);
                $tblItemAllByCommodity = Commodity::useService()->getItemAllByCommodity($tblCommodity);
                $tblItemAll = Item::useService()->getItemAll();

                if (!empty( $tblItemAllByCommodity )) {
                    $tblItemAll = array_udiff($tblItemAll, $tblItemAllByCommodity,
                        function (TblItem $ObjectA, TblItem $ObjectB) {

                            return $ObjectA->getId() - $ObjectB->getId();
                        }
                    );
                }

                if (!empty( $tblCommodityItem )) {
                    array_walk($tblCommodityItem, function (TblCommodityItem $tblCommodityItem) {

                        $tblItem = $tblCommodityItem->getTblItem();
                        $tblItemConditionList = Item::useService()->getCalculationAllByItem($tblItem);
                        $tblCommodityItem->PriceString = '';

                        $tblCommodityItem->Name = $tblItem->getName();
                        $tblCommodityItem->Description = $tblItem->getDescription();
                        if(!empty($tblItemConditionList))
                        {
                            $tblCommodityItem->PriceString = $tblItemConditionList[0]->getPriceString();
                        }
                        $tblCommodityItem->TotalPriceString = $tblCommodityItem->getTotalPriceString();
                        $tblCommodityItem->QuantityString = str_replace('.', ',', $tblCommodityItem->getQuantity());
                        $tblCommodityItem->Option =
                            (new \SPHERE\Common\Frontend\Link\Repository\Primary('Entfernen', '/Billing/Inventory/Commodity/Item/Remove',
                                new Minus(), array(
                                    'Id' => $tblCommodityItem->getId()
                                )))->__toString();
                    });
                }

                if (!empty( $tblItemAll )) {
                    /** @var TblItem $tblItem */
                    foreach ($tblItemAll as $tblItem) {
                        $tblItemConditionList = Item::useService()->getCalculationAllByItem($tblItem);
                        $tblCommodityItem->PriceString = '';
                        if(!empty($tblItemConditionList))
                        {
                            $tblCommodityItem->PriceString = $tblItemConditionList[0]->getPriceString();
                        }
                        $tblItem->Option =
                            (new Form(
                                new FormGroup(
                                    new FormRow(array(
                                        new FormColumn(
                                            new TextField('Item[Quantity]', 'Menge', '', new Quantity()
                                            )
                                            , 7),
                                        new FormColumn(
                                            new Primary('Hinzufügen',
                                                new Plus())
                                            , 5)
                                    ))
                                ), null,
                                '/Billing/Inventory/Commodity/Item/Add', array(
                                    'tblCommodityId' => $tblCommodity->getId(),
                                    'tblItemId'      => $tblItem->getId()
                                )
                            ))->__toString();
                    }
                }

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Name', $tblCommodity->getName(), Panel::PANEL_TYPE_SUCCESS), 4
                                ),
                                new LayoutColumn(
                                    new Panel('Beschreibung', $tblCommodity->getDescription(),
                                        Panel::PANEL_TYPE_SUCCESS), 8
                                )
                            ))
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                        new TableData($tblCommodityItem, null,
                                            array(
                                                'Name'             => 'Name',
                                                'Description'      => 'Beschreibung',
                                                'PriceString'      => 'Preis',
                                                'QuantityString'   => 'Menge',
                                                'TotalPriceString' => 'Gesamtpreis',
                                                'Option'           => ''
                                            )
                                        )
                                    )
                                )
                            )),
                        ), new Title('vorhandene Artikel')),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                        new TableData($tblItemAll, null,
                                            array(
                                                'Name'        => 'Name',
                                                'Description' => 'Beschreibung',
                                                'CostUnit'    => 'Kostenstelle',
                                                'PriceString' => 'Preis',
                                                'Option'      => 'Anzahl'
                                            )
                                        )
                                    )
                                )
                            )),
                        ), new Title('mögliche Artikel'))
                    ))
                );
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendItemRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Artikel Entfernen');
        $tblCommodityItem = Commodity::useService()->getCommodityItemById($Id);
        if (!empty( $tblCommodityItem )) {
            $Stage->setContent(Commodity::useService()->removeItemToCommodity($tblCommodityItem));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendItemAccountRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('FIBU-Konto Entfernen');
        $tblItemAccount = Item::useService()->getItemAccountById($Id);
        if (!empty( $tblItemAccount )) {
            $Stage->setContent(Item::useService()->removeItemAccount($tblItemAccount));
        }

        return $Stage;
    }

    /**
     * @param $tblItemId
     * @param $tblAccountId
     *
     * @return Stage
     */
    public function frontendItemAccountAdd($tblItemId, $tblAccountId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Artikel');
        $Stage->setDescription('FIBU-Konto Hinzufügen');
        $tblItem = Item::useService()->getItemById($tblItemId);
        $tblAccount = Account::useService()->getAccountById($tblAccountId);

        if (!empty( $tblItemId ) && !empty( $tblAccountId )) {
            $Stage->setContent(Item::useService()->addItemToAccount($tblItem, $tblAccount));
        }

        return $Stage;
    }
}
