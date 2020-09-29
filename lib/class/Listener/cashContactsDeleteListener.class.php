<?php

/**
 * Class cashContactsDeleteListener
 */
class cashContactsDeleteListener extends waEventHandler
{
    /**
     * @param int[] $event
     */
    public function execute(&$event)
    {
        $contactIds = $event;

        cash()->getModel(cashTransaction::class)->updateByField(
            ['contractor_contact_id' => $contactIds],
            ['contractor_contact_id' => null]
        );
        cash()->getModel(cashTransaction::class)->updateByField(
            ['create_contact_id' => $contactIds],
            ['create_contact_id' => null]
        );

        cash()->getModel(cashRepeatingTransaction::class)->updateByField(
            ['contractor_contact_id' => $contactIds],
            ['contractor_contact_id' => null]
        );
        cash()->getModel(cashRepeatingTransaction::class)->updateByField(
            ['create_contact_id' => $contactIds],
            ['create_contact_id' => null]
        );
    }

    /**
     * @param array $event
     *
     * @return array
     * @throws waException
     */
    public function links(&$event)
    {
        $contactIds = $event;

        $links = [];
        foreach ($contactIds as $contactId) {
            $contractor = cash()->getModel(cashTransaction::class)->countByField('contractor_contact_id', $contactId);
            if ($contractor) {
                $links[$contactId][] = ['role' => _w('Transaction contractor'), 'links_number' => $contractor];
            }

            $creator = cash()->getModel(cashTransaction::class)->countByField('create_contact_id', $contactId);
            if ($creator) {
                $links[$contactId][] = ['role' => _w('Transaction author'), 'links_number' => $creator];
            }
        }

        return $links;
    }
}
