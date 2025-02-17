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

    'accepted'             => 'The :attribute must be accepted.',
    'phone_number'         => 'The :attribute may only contain digits, minimum 3 and maximum 15.',
    'active_url'           => 'The :attribute is not a valid URL.',
    'after'                => 'The :attribute must be a date after :date.',
    'after_or_equal'       => 'The :attribute must be a date after or equal to :date.',
    'alpha'                => 'The :attribute may only contain letters.',
    'alpha_dash'           => 'The :attribute may only contain letters, numbers, and dashes.',
    'alpha_num'            => 'The :attribute may only contain letters and numbers.',
    'array'                => 'The :attribute field must be an array.',
    'before'               => 'The :attribute must be a date before :date.',
    'before_or_equal'      => 'The :attribute must be a date before or equal to :date.',
    'between'              => [
        'numeric' => 'The :attribute must be between :min and :max.',
        'file'    => 'The :attribute must be between :min and :max kilobytes.',
        'string'  => 'The :attribute must be between :min and :max characters.',
        'array'   => 'The :attribute must have between :min and :max items.',
    ],
    'boolean'              => 'The :attribute field must be true or false.',
    'confirmed'            => 'The :attribute confirmation does not match.',
    'date'                 => 'The :attribute field is not a valid date.',
    'date_format'          => 'The :attribute does not match the format :format.',
    'different'            => 'The :attribute and :other must be different.',
    'digits'               => 'The :attribute must be :digits digits.',
    'digits_between'       => 'The :attribute must be between :min and :max digits.',
    'dimensions'           => 'The :attribute has invalid image dimensions.',
    'distinct'             => 'The :attribute field has a duplicate value.',
    'email'                => 'The :attribute must be a valid email address.',
    'exists'               => 'The selected :attribute is invalid.',
    'file'                 => 'The :attribute must be a file.',
    'filled'               => 'The :attribute field must have a value.',
    'image'                => 'The :attribute must be an image.',
    'in'                   => 'The selected :attribute is invalid.',
    'in_array'             => 'The :attribute field does not exist in :other.',
    'integer'              => 'The :attribute field must be an integer.',
    'ip'                   => 'The :attribute must be a valid IP address.',
    'ipv4'                 => 'The :attribute must be a valid IPv4 address.',
    'ipv6'                 => 'The :attribute must be a valid IPv6 address.',
    'json'                 => 'The :attribute must be a valid JSON string.',
    'max'                  => [
        'numeric' => 'The :attribute may not be greater than :max.',
        'file'    => 'The :attribute may not be greater than :max kilobytes.',
        'string'  => 'The :attribute may not be greater than :max characters.',
        'array'   => 'The :attribute may not have more than :max items.',
    ],
    'mimes'                => 'The :attribute must be a file of type: :values.',
    'mimetypes'            => 'The :attribute must be a file of type: :values.',
    'min'                  => [
        'numeric' => 'The :attribute must be at least :min.',
        'file'    => 'The :attribute must be at least :min kilobytes.',
        'string'  => 'The :attribute must be at least :min characters.',
        'array'   => 'The :attribute must have at least :min items.',
    ],
    'not_in'               => 'The selected :attribute is invalid.',
    'numeric'              => 'The :attribute must be a number.',
    'present'              => 'The :attribute field must be present.',
    'regex'                => 'The :attribute format is invalid.',
    'required'             => 'The :attribute field is required.',
    'required_if'          => 'The :attribute field is required when :other is :value.',
    'required_unless'      => 'The :attribute field is required unless :other is in :values.',
    'required_with'        => 'The :attribute field is required when :values is present.',
    'required_with_all'    => 'The :attribute field is required when :values is present.',
    'required_without'     => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same'                 => 'The :attribute and :other must match.',
    'size'                 => [
        'numeric' => 'The :attribute must be :size.',
        'file'    => 'The :attribute must be :size kilobytes.',
        'string'  => 'The :attribute must be :size characters.',
        'array'   => 'The :attribute must contain :size items.',
    ],
    'string'               => 'The :attribute field must be a string.',
    'timezone'             => 'The :attribute must be a valid zone.',
    'unique'               => 'The :attribute has already been taken.',
    'uploaded'             => 'The :attribute failed to upload.',
    'url'                  => 'The :attribute format is invalid.',

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
        'name.*' => 'name',
        'period_from' => 'period from',
        'period_to' => 'period to',
        'username' => 'username',
        'precept' => 'Precept',
        'module' => 'module',
        'action' => 'action',
        'category_ids' => 'category ids',
        'tag_ids' => 'tag ids',
        'org_ids' => 'org ids',
        'group_ids' => 'group ids',
        'user_ids' => 'user ids',
        'dataset_ids' => 'dataset ids',
        'resource_uris' => 'resource uris',
        'ip_adress' => 'ip adress',
        'active' => 'active',
        'type' => 'type',
        'field' => 'field',
        'category_id' => 'category id',
        'request_id' => 'request id',
        'org_id' => 'org id',
        'status' => 'status',
        'date_from' => 'date from',
        'date_to' => 'date to',
        'search' => 'search',
        'order' => 'order',
        'tag_id' => 'tag id',
        'format' => 'format',
        'reported' => 'reported',
        'created_by' => 'created by',
        'keywords' => 'keywords',
        'user_id' => 'user id',
        'locale' => 'locale',
        'valid' => 'valid',
        'date_type' => 'date type',
        'records_per_page' => 'records per page',
        'page_number' => 'page number',
        'icon_filename' => 'icon filename',
        'icon_mimetype' => 'icon mimetype',
        'icon_data' => 'icon data',
        'category_id' => 'category id',
        'org_id' => 'org id',
        'description' => 'description',
        'email' => 'email',
        'published_url' => 'published url',
        'contact_name' => 'contact name',
        'notes' => 'notes',
        'status' => 'status',
        'filename' => 'filename',
        'mimetype' => 'mimetype',
        'data' => 'data',
        'title' => 'title',
        'abstract' => 'abstract',
        'body' => 'body',
        'head_title' => 'head title',
        'meta_description' => 'meta description',
        'meta_keywords' => 'meta keywords',
        'forum_link' => 'forum link',
        'valid_from' => 'valid from',
        'valid_to' => 'valid to',
        'section_id' => 'section id',
        'data.schema_description' => 'schema',
        'data.schema_url' => 'schema url',
        'data.type' => 'type',
        'resource url' => 'resource url',
        'http_rq_type' => 'http request type',
        'authentication' => 'authentication',
        'http_headers' => 'http headers',
        'post_data' => 'post data',
        'custom_fields' => 'custom fields',
        'label' => 'label',
        'value' => 'value',
        'resource_id' => 'resource id',
        'password' => 'password',
        'password_confirm' => 'password confirm',
        'add_info' => 'add info',
        'firstname' => 'name',
        'lastname' => 'family name',
        'name.bg' => 'name in Bulgarian',
        'locale' => 'locale',
        'tag_id' => 'tag id',
        'data' => 'data',
        'request_id' => 'request id',
        'org_id' => 'org id',
        'terms_of_use_id' => 'terms of use id',
        'visibility' => 'visibility',
        'source' => 'source',
        'version' => 'version',
        'author_name' => 'author name',
        'author_email' => 'author e-mail',
        'support_name' => 'support name',
        'support_email' => 'contact e-mail',
        'dataset_uri' => 'dataset uri',
        'status' => 'status',
        'data_set_uri' => 'dataset uri',
        'group_id' => 'group id',
        'doc_id' => 'doc id',
        'news_id' => 'news id',
        'type' => 'type',
        'approved' => 'approved',
        'parent_org_id' => 'parent organisation id',
        'logo_mimetype' => 'logo mimetype',
        'logo_filename' => 'logo filename',
        'logo' => 'logo',
        'logo_data' => 'logo data',
        'role_id' => 'role id',
        'keywords' => 'keywords',
        'for_approval' => 'for approval',
        'user_id' => 'user id',
        'page_id' => 'page id',
        'resource_uri' => 'resource uri',
        'query' => 'query',
        'format' => 'format',
        'namespaces' => 'namespaces',
        'id' => 'id',
        'parent_id' => 'parent id',
        'read_only' => 'read only',
        'theme' => 'theme',
        'signal_id' => 'signal id',
        'description' => 'description',
        'is_default' => 'is default',
        'terms_id' => 'terms id',
        'is_admin' => 'is admin',
        'hash' => 'hash',
        'data_set_id' => 'dataset id',
        'follow_user_id' => 'follow user id',
        'news' => 'news',
        'schema_description' => 'schema',
        'schema_url' => 'schema url',
        'category_id' => 'main topic',
        'img_file' => 'file',
        'img_url' => 'url to file',
        'comment' => 'comment',
        'mime_type' => 'mime type',
        'color' => 'color',
        'phone' => 'contact phone',
        'trusted' => 'post without approval',
    ],

];
