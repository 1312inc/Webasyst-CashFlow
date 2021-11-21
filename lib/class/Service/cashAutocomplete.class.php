<?php

/**
 * Class cashAutocomplete
 */
class cashAutocomplete
{
    /**
     * Thanks to shop \shopBackendAutocompleteController::contactsAutocomplete
     *
     * @param cashAutocompleteParamsDto $params
     * @param null|int                  $limit
     *
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function findContacts(cashAutocompleteParamsDto $params, int $limit = null): array
    {
        $limit = $limit ?? 5;
        $key = sprintf('%s|%s|%s', $params->getTerm(), $params->getCategoryId(), (string) $limit);
        $found = cash()->getCache()->get($key);
        if ($found !== null) {
            return $found;
        }

        $q = $params->getTerm();
        $m = cash()->getModel();
        $result = [];

        if ($q) {
            // The plan is: try queries one by one (starting with fast ones),
            // until we find 5 rows total.
            $sqls = [];
            $search_terms = [];   // by what term was search in current sql, need for highlighting

            // Name starts with requested string
            $sqls[] = "SELECT c.id, c.name, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                   WHERE c.name LIKE '" . $m->escape($q, 'like') . "%'
                   LIMIT {LIMIT}";
            $search_terms[] = $q;

            $name_ar = preg_split('/\s+/', $q);
            if (count($name_ar) > 1) {
                $name_condition =
                    "((c.firstname LIKE '%" . $m->escape(
                        $name_ar[0],
                        'like'
                    ) . "%' AND c.lastname LIKE '%" . $m->escape(
                        $name_ar[1],
                        'like'
                    ) . "%')
                    OR (c.firstname LIKE '%" . $m->escape(
                        $name_ar[1],
                        'like'
                    ) . "%' AND c.lastname LIKE '%" . $m->escape($name_ar[0], 'like') . "%'))";
                $sqls[] = "SELECT c.id, c.name, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                   WHERE $name_condition
                   LIMIT {LIMIT}";
                $search_terms[] = $q;
            }

            $name_condition = "c.name LIKE '_%" . $m->escape($q, 'like') . "%'";
            $sqls[] = "SELECT c.id, c.name, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                   WHERE $name_condition
                   LIMIT {LIMIT}";
            $search_terms[] = $q;

            // Email starts with requested string
            $sqls[] = "SELECT c.id, c.name, e.email, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                       JOIN wa_contact_emails AS e
                           ON e.contact_id=c.id
                   WHERE e.email LIKE '" . $m->escape($q, 'like') . "%'
                   LIMIT {LIMIT}";
            $search_terms[] = $q;

            // Phone contains requested string
            if (preg_match('~^[wp0-9\-\+\#\*\(\)\. ]+$~', $q)) {
                $query_phone = waContactPhoneField::cleanPhoneNumber($q);

                // search sql template
                $sql_template = "SELECT c.id, c.name, d.value as phone, c.firstname, c.middlename, c.lastname, c.photo
                       FROM wa_contact AS c
                           JOIN wa_contact_data AS d
                               ON d.contact_id=c.id AND d.field='phone'
                       WHERE {CONDITION}
                       LIMIT {LIMIT}";

                // search as prefix
                $condition_rule = "d.value LIKE '{PHONE}%'";

                // first of all search by query phone as it
                $sql_t = str_replace("{CONDITION}", $condition_rule, $sql_template);
                $sql = str_replace("{PHONE}", $query_phone, $sql_t);
                $sqls[] = $sql;
                $search_terms[] = $query_phone;

                // than try apply transformation and than search by transform phone

                $is_international = substr($q, 0, 1) === '+';

                // apply transformations for all domains
                $transform_results = waDomainAuthConfig::transformPhonePrefixForDomains(
                    $query_phone,
                    $is_international
                );
                $transform_results = array_filter(
                    $transform_results,
                    static function ($result) {
                        return $result['status'];   // status == true, so phone is changed
                    }
                );

                // unique phones that changed after transformation
                $phones = waUtils::getFieldValues($transform_results, 'phone');

                if ($phones) {
                    $condition = [];
                    foreach ($phones as $phone) {
                        $condition[] = str_replace('{PHONE}', $phone, $condition_rule);
                    }
                    $condition = '(' . join(' OR ', $condition) . ')';
                    $sql = str_replace('{CONDITION}', $condition, $sql_template);

                    $sqls[] = $sql;
                    $search_terms[] = $phones;
                }

                // search as substring
                $condition_rule = "d.value LIKE '%{PHONE}%'";

                // search only by query phone as it, without transformation
                $sql_t = str_replace("{CONDITION}", $condition_rule, $sql_template);
                $sql = str_replace("{PHONE}", $query_phone, $sql_t);
                $sqls[] = $sql;
                $search_terms[] = $query_phone;

            }

            // Name contains requested string
            $sqls[] = "SELECT c.id, c.name, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                   WHERE c.name LIKE '_%" . $m->escape($q, 'like') . "%'
                   LIMIT {LIMIT}";
            $search_terms[] = $q;

            // Email contains requested string
            $sqls[] = "SELECT c.id, c.name, e.email, c.firstname, c.middlename, c.lastname, c.photo
                   FROM wa_contact AS c
                       JOIN wa_contact_emails AS e
                           ON e.contact_id=c.id
                   WHERE e.email LIKE '_%" . $m->escape($q, 'like') . "%'
                   LIMIT {LIMIT}";
            $search_terms[] = $q;

            foreach ($sqls as $index => $sql) {
                if (count($result) >= $limit) {
                    break;
                }

                foreach ($m->query(str_replace('{LIMIT}', $limit, $sql)) as $c) {
                    if (!empty($result[$c['id']])) {
                        continue;
                    }

                    if (!empty($c['firstname']) || !empty($c['middlename']) || !empty($c['lastname'])) {
                        $c['name'] = waContactNameField::formatName($c);
                    }

                    $name = htmlspecialchars($c['name'], ENT_QUOTES, 'utf-8');
                    $email = htmlspecialchars(ifset($c['email'], ''), ENT_QUOTES, 'utf-8');
                    $phone = htmlspecialchars(ifset($c['phone'], ''), ENT_QUOTES, 'utf-8');

                    $terms = (array) $search_terms[$index];
                    foreach ($terms as $term) {
                        $term_safe = htmlspecialchars($term);
                        $match = false;

                        if ($this->match($name, $term_safe)) {
                            $name = $this->prepare($name, $term_safe, false);
                            $match = true;
                        }

                        if ($this->match($email, $term_safe)) {
                            $email = $this->prepare($email, $term_safe, false);
                            if ($email) {
                                $email = '<i class="icon16 email"></i>' . $email;
                            }
                            $match = true;
                        }

                        if ($this->match($phone, $term_safe)) {
                            $phone = $this->prepare($phone, $term_safe, false);
                            if ($phone) {
                                $phone = '<i class="icon16 phone"></i>' . $phone;
                            }
                            $match = true;
                        }

                        if ($match) {
                            break;
                        }
                    }

                    $result[$c['id']] = $this->prepareData($c, $name, $email, $phone);

                    if (count($result) >= $limit) {
                        break 2;
                    }
                }
            }
        }

        if ($params->getCategoryId()) {
            $sql = <<<SQL
SELECT ct.contractor_contact_id id, MAX(ct.create_datetime) last_cash_time, c.name, c.firstname, c.middlename, c.lastname, c.photo
FROM cash_transaction ct
JOIN wa_contact c ON ct.contractor_contact_id = c.id
WHERE ct.contractor_contact_id IS NOT NULL
AND ct.category_id = i:categoryId
GROUP BY ct.contractor_contact_id
ORDER BY MAX(ct.create_datetime) DESC
LIMIT i:limit
SQL;
            $categoryContacts = $m->query($sql, ['categoryId' => $params->getCategoryId(), 'limit' => $limit])
                ->fetchAll('id');

            foreach ($result as $contactId => $contact) {
                if (!isset($categoryContacts[$contactId])) {
                    unset($result[$contactId]);
                    continue;
                }

                $result[$contactId]['last_cash_time'] = $categoryContacts[$contactId]['last_cash_time'];
            }

            if (!$result) {
                foreach ($categoryContacts as $contactId => $contact) {
                    $result[$contactId] = $this->prepareData($contact);
                    $result[$contactId]['last_cash_time'] = $contact['last_cash_time'];
                }
            }

            usort($result, static function ($c1, $c2) {
                return -($c1['last_cash_time'] <=> $c2['last_cash_time']);
            });
        }

        $found = array_values($result);
        cash()->getCache()->set($key, $found, 30);

        return $found;
    }


    /**
     * @param      $str
     * @param      $term_safe
     * @param bool $escape
     *
     * @return string|string[]|null
     */
    private function prepare($str, $term_safe, $escape = true)
    {
        $pattern = '~(' . preg_quote($term_safe, '~') . ')~ui';
        $template = '<span class="bold highlighted">\1</span>';
        if ($escape) {
            $str = htmlspecialchars($str, ENT_QUOTES, 'utf-8');
        }

        return preg_replace($pattern, $template, $str);
    }

    /**
     * @param      $str
     * @param      $term_safe
     * @param bool $escape
     *
     * @return false|int
     */
    private function match($str, $term_safe, $escape = true)
    {
        if ($escape) {
            $str = htmlspecialchars($str, ENT_QUOTES, 'utf-8');
        }

        return preg_match('~(' . preg_quote($term_safe, '~') . ')~ui', $str);
    }

    private function prepareData(array $c, $name = null, $email = null, $phone = null): array
    {
        $photo = waContact::getPhotoUrl($c['id'], $c['photo'], 96);

        return [
            'id' => $c['id'],
            'value' => $c['id'],
            'name' => $c['name'],
            'firstname' => $c['firstname'],
            'middlename' => $c['middlename'],
            'lastname' => $c['lastname'],
            'photo_url' => $photo,
            'photo_url_absolute' => wa()->getConfig()->getHostUrl() . $photo,
            'label' => sprintf(
                '<i class="icon16 userpic20" style="background-image: url(%s);"></i>%s',
                waContact::getPhotoUrl($c['id'], $c['photo'], 20),
                implode(' ', array_filter([$name, $email, $phone]))
            ),
        ];
    }
}
