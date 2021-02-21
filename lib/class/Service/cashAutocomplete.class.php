<?php

/**
 * Class cashAutocomplete
 */
class cashAutocomplete
{
    /**
     * Thanks to shop \shopBackendAutocompleteController::contactsAutocomplete
     *
     * @param string $q
     * @param null|int $limit
     *
     * @return array
     * @throws waException
     */
    public function findContacts($q, $limit = null)
    {
        $q = trim($q);
        $key = sprintf('%s|%s', $q, (string) $limit);
        $found = cash()->getCache()->get($key);
        if ($found !== null) {
            return $found;
        }

        $m = cash()->getModel();

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
        if (count($name_ar) == 2) {
            $name_condition =
                "((c.firstname LIKE '%" . $m->escape($name_ar[0], 'like') . "%' AND c.lastname LIKE '%" . $m->escape(
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
            $transform_results = waDomainAuthConfig::transformPhonePrefixForDomains($query_phone, $is_international);
            $transform_results = array_filter(
                $transform_results,
                function ($result) {
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

        $limit = $limit !== null ? $limit : 5;
        $result = [];
        foreach ($sqls as $index => $sql) {
            if (count($result) >= $limit) {
                break;
            }

            foreach ($m->query(str_replace('{LIMIT}', $limit, $sql)) as $c) {
                if (empty($result[$c['id']])) {
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

                    $photo = waContact::getPhotoUrl($c['id'], $c['photo'], 96);
                    $result[$c['id']] = [
                        'id' => $c['id'],
                        'value' => $c['id'],
                        'name' => $c['name'],
                        'firstname' => $c['firstname'],
                        'lastname' => $c['lastname'],
                        'photo_url' => waContact::getPhotoUrl($c['id'], $c['photo'], 96),
                        'photo_url_absolute' => rtrim(wa()->getUrl(true), '/') . $photo,
                        'label' => implode(' ', array_filter([$name, $email, $phone])),
                    ];

                    if (count($result) >= $limit) {
                        break 2;
                    }
                }
            }
        }

        foreach ($result as &$c) {
            $contact = new waContact($c['id']);
            $userpic = $contact->getPhoto(20);
            $c['label'] = sprintf(
                '<i class="icon16 userpic20" style="background-image: url(%s);"></i>%s',
                $userpic,
                $c['label']
            );
        }
        unset($c);

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
    protected function prepare($str, $term_safe, $escape = true)
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
    protected function match($str, $term_safe, $escape = true)
    {
        if ($escape) {
            $str = htmlspecialchars($str, ENT_QUOTES, 'utf-8');
        }

        return preg_match('~(' . preg_quote($term_safe, '~') . ')~ui', $str);
    }
}
