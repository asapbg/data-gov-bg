<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Поле :attribute трябва да бъде прието.',
    'active_url'           => 'Поле :attribute не е валиден URL.',
    'after'                => 'Поле :attribute трябва да бъде след :date.',
    'after_or_equal'       => 'Поле :attribute трябва да бъде дата след или равна на :date.',
    'alpha'                => 'Поле :attribute може да съдържа само букви.',
    'alpha_dash'           => 'Поле :attribute може да съдържа само букви, числа и тирета.',
    'alpha_num'            => 'Поле :attribute може да съдържа само букви и числа.',
    'array'                => 'Поле :attribute трябва да бъде масив.',
    'before'               => 'Поле :attribute трябва да бъде дата преди :date.',
    'before_or_equal'      => 'Поле :attribute трябва да бъде дата преди или равна на :date.',
    'between'              => [
        'numeric' => 'Поле :attribute трябва да бъде между :min и :max.',
        'file'    => 'Поле :attribute трябва да бъде между :min и :max килобайта.',
        'string'  => 'Поле :attribute трябва да бъде между :min и :max символа.',
        'array'   => 'Поле :attribute трябва да бъде между :min и :max елемента.',
    ],
    'boolean'              => 'Поле :attribute трябва да бъде true или false.',
    'confirmed'            => 'Поле :attribute потвърждението не съвпада.',
    'date'                 => 'Поле :attribute не е валидна дата.',
    'date_format'          => 'Поле :attribute не съвпада с формат :format.',
    'different'            => 'Поле :attribute и :other трябва да бъдат различни.',
    'digits'               => 'Поле :attribute трябва да бъде :digits цифри.',
    'digits_between'       => 'Поле :attribute трябва да бъде между цифрите :min и :max.',
    'dimensions'           => 'Поле :attribute е с невалидни размери.',
    'distinct'             => 'Поле :attribute има дублирана стойност.',
    'email'                => 'Поле :attribute трябва да бъде валиден имейл адрес.',
    'exists'               => 'Поле :attribute е невалидно.',
    'file'                 => 'Поле :attribute трябва да бъде файл.',
    'filled'               => 'Поле :attribute трябва да има стойност.',
    'image'                => 'Поле :attribute трябва да бъде картинка.',
    'in'                   => 'Поле :attribute е невалидно.',
    'in_array'             => 'Поле :attribute не съществува в :other.',
    'integer'              => 'Поле :attribute трябва да бъде цяло число.',
    'ip'                   => 'Поле :attribute трябва да бъде валиден IP адрес.',
    'ipv4'                 => 'Поле :attribute трябва да бъде валиден IPv4 адрес.',
    'ipv6'                 => 'Поле :attribute трябва да бъде валиден IPv6 адрес.',
    'json'                 => 'Поле :attribute трябва да бъде валиден JSON низ.',
    'max'                  => [
        'numeric' => 'Поле :attribute не може да бъде по голямо от :max.',
        'file'    => 'Поле :attribute не може да бъде повече от :max килобайта.',
        'string'  => 'Поле :attribute не може да бъде повече от :max символа.',
        'array'   => 'Поле :attribute не може да бъде повече от :max елемента.',
    ],
    'mimes'                => 'Поле :attribute трябва да бъде файлов тип: :values.',
    'mimetypes'            => 'Поле :attribute трябва да бъде файлов тип: :values.',
    'min'                  => [
        'numeric' => 'Поле :attribute трябва да бъде поне :min.',
        'file'    => 'Поле :attribute трябва да бъде поне :min килобайта.',
        'string'  => 'Поле :attribute трябва да бъде поне :min символа.',
        'array'   => 'Поле :attribute трябва да бъде поне :min елемента.',
    ],
    'not_in'               => 'Поле :attribute е невалидно.',
    'numeric'              => 'Поле :attribute трябва да бъде число.',
    'present'              => 'Поле :attribute трябва да фигурира.',
    'regex'                => 'Форматът на :attribute е невалиден.',
    'required'             => 'Поле :attribute е задължително.',
    'required_if'          => 'Поле :attribute е задължително когато :other е :value.',
    'required_unless'      => 'Поле :attribute е задължително освен ако :other е в :values.',
    'required_with'        => 'Поле :attribute е задължително когато :values е налично.',
    'required_with_all'    => 'Поле :attribute е задължително когато :values е налично.',
    'required_without'     => 'Поле :attribute е задължително когато :values не е налично.',
    'required_without_all' => 'Поле :attribute е задължително когато нито едно от :values е налично.',
    'same'                 => 'Поле :attribute и поле :other трябва да са еднакви.',
    'size'                 => [
        'numeric' => 'Поле :attribute трябва да е :size.',
        'file'    => 'Поле :attribute трябва да е :size килобайта.',
        'string'  => 'Поле :attribute трябва да е :size символа.',
        'array'   => 'Поле :attribute трябва да съдържа :size елемента.',
    ],
    'string'               => 'Поле :attribute трябва да бъде низ.',
    'timezone'             => 'Поле :attribute трябва да бъде валидна зона.',
    'unique'               => 'Поле :attribute е заето.',
    'uploaded'             => 'Файлът :attribute не е качен.',
    'url'                  => 'Форматът на поле :attribute е невалиден.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'username' => 'потребителско име',
        'password' => 'парола',
        'firstname' => 'име',
        'lastname' => 'фамилия',
        'email' => 'имейл адрес',
        'password_confirm' => 'потвърди парола',
        'name' => 'наименование',
        'name.*' => 'наименование',
        'category_id' => 'основна тема',
        'criteria' => 'критерия',
        'criteria.period_from' => 'от период',
        'criteria.period_to' => 'до период',
        'criteria.username' => 'потребителско име',
        'criteria.module' => 'модул',
        'criteria.action' => 'действие',
        'criteria.category_ids' => 'категории',
        'criteria.tag_ids' => 'тагове',
        'criteria.org_ids' => 'организации',
        'criteria.group_ids' => 'групи',
        'criteria.user_ids' => 'потребители',
        'criteria.dataset_ids' => 'набори данни',
        'criteria.resource_uris' => 'ресурси',
        'criteria.ip_adress' => 'IP адрес',
        'criteria.active' => 'активен',
        'criteria.order.type' => 'тип подредба',
        'criteria.order.field' => 'колона подредба',
        'criteria.category_id' => 'основна тема',
        'criteria.request_id' => 'заявка',
        'criteria.org_id' => 'организация',
        'criteria.status' => 'статус',
        'criteria.date_from' => 'дата от',
        'criteria.date_to' => 'дата до',
        'criteria.search' => 'търси',
        'criteria.order' => 'подредба',
        'criteria.group_id' => 'група',
        'criteria.tag_id' => 'таг',
        'criteria.format' => 'формат',
        'criteria.terms_of_use_id' => 'условия за ползване',
        'criteria.reported' => 'докладван',
        'criteria.created_by' => 'създаден от',
        'criteria.keywords' => 'ключови думи',
        'criteria.user_id' => 'потребител',
        'criteria.doc_id' => 'документ',
        'criteria.valid' => 'валиден',
        'criteria.date_type' => 'дата тип',
        'criteria.approved' => 'одобрен',
        'criteria.page_id' => 'страница',
        'criteria.resource_uri' => 'уникален идентификатор на ресурс',
        'criteria.dataset_uri' => 'уникален идентификатор на набор от данни',
        'criteria.id' => 'идентификатор',
        'criteria.section_id' => 'секция',
        'criteria.signal_id' => 'сигнал',
        'criteria.is_admin' => 'администратор',
        'records_per_page' => 'записи за страница',
        'page_number' => 'номер на страница',
        'ordering' => 'подредба',
        'active' => 'активен',
        'icon_data' => 'данни за икона',
        'icon_mimetype' => 'тип икона',
        'icon_filename' => 'име на икона',
        'icon' => 'икона',
        'data.name' => 'наименование',
        'data.locale' => 'локал',
        'data.icon' => 'икона',
        'data.icon_filename' => 'име на икона',
        'data.icon_mimetype' => 'тип на икона',
        'data.icon_data' => 'данни за икона' ,
        'data.active' => 'активен',
        'data.ordering' => 'подредба',
        'data.category_id' => 'основна тема',
        'data.org_id' => 'организация',
        'data.description' => 'описание',
        'data.email' => 'имейл',
        'data.published_url' => 'url на публикуване',
        'data.contact_name' => 'име за контакт',
        'data.notes' => 'бележки',
        'data.status' => 'статус',
        'data.filename' => 'име на файл',
        'data.mimetype' => 'тип на файл',
        'data.data' => 'данни',
        'data.title' => 'заглавие',
        'data.abstract' => 'абстракт',
        'data.head_title' => 'основно заглавие',
        'data.meta_description' => 'мета описание',
        'data.meta_keywords' => 'мета ключови думи',
        'data.forum_link' => 'форумен линк',
        'data.valid_from' => 'валиден от',
        'data.valid_to' => 'валиден до',
        'data.body' => 'тяло',
        'data.section_id' => 'секция',
        'data.schema_description' => 'описание на схема',
        'data.schema_url' => 'адрес на схема',
        'data.type' => 'тип',
        'data.resource_url' => 'адрес на ресурс',
        'data.http_rq_type' => 'тип на заявка',
        'data.authentication' => 'автентикация',
        'data.http_headers' => 'хедъри',
        'data.post_data' => 'пост данни',
        'data.custom_fields' => 'персонални полета',
        'data.custom_fields.label' => 'етикет',
        'data.custom_fields.value' => 'стойност',
        'data.version' => 'версия',
        'data.resource_uri' => 'уникален идентификатор на ресурс',
        'data.*.module_name' => 'име на модул',
        'data.*.right' => 'право',
        'data.*.limit_to_own_data' => 'ограничи до собствени данни',
        'data.*.api' => 'достъпно от апи',
        'data.parent_id' => 'родител',
        'data.read_only' => 'само за четене',
        'data.theme' => 'тема',
        'data.resource_id' => 'ресурс',
        'data.firstname' => 'име',
        'data.lastname' => 'фамилия',
        'data.is_default' => 'по подразбиране',
        'data.is_admin' => 'администратор',
        'data.password' => 'парола',
        'data.password_confirm' => 'потвърди парола',
        'data.add_info' => 'добави информация',
        'tag_id' => 'таг',
        'data' => 'данни',
        'es_id' => 'еластик сет',
        'request_id' => 'заявка',
        'org_id' => 'организация',
        'uri' => 'уникален идентификатор за ресурс',
        'terms_of_use_id' => "условия за ползване",
        'visibility' => 'видимост',
        'source' => 'източник',
        'version' => 'версия',
        'author_name' => 'име на автор',
        'author_email' => 'имейл на автор',
        'support_name' => 'поддръжка име',
        'support_email' => 'поддръжка имейл',
        'dataset_uri' => 'уникален идентификатор на набор от данни',
        'status' => 'статус',
        'data_set_uri' => 'уникален идентификатор на набор от данни',
        'group_id' => 'група',
        'doc_id' => 'документ',
        'news_id' => 'новина',
        'type' => 'тип',
        'approved' => 'одобрен',
        'parent_org_id' => 'родителска организация',
        'logo_mimetype' => 'лого тип',
        'logo_filename' => 'лого име',
        'logo' => 'лого',
        'logo_data' => 'данни за лого',
        'role_id' => 'роля',
        'keywords' => 'ключови думи',
        'for_approval' => 'за одобрение',
        'user_id' => 'потребител',
        'page_id' => 'страница',
        'resource_uri' => 'уникален идентификатор на ресурс',
        'query' => 'заявка',
        'format' => 'формат',
        'namespaces' => 'пространство от имена',
        'id' => 'идентификатор',
        'read_only' => 'само за четене',
        'theme' => 'тема',
        'locale' => 'локал',
        'parent_id' => 'родител',
        'signal_id' => 'сигнал',
        'description' => 'описание',
        'is_default' => 'по подразбиране',
        'terms_id' => 'условия за ползване',
        'is_admin' => 'администратор',
        'hash' => 'хаш',
        'data_set_id' => 'набор данни',
        'follow_user_id' => 'следвай потребител',
        'news' => 'следвай новини',

    ],

];
