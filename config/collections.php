<?php

/**
 * EAIFR content model — eight collections plus a contact form.
 * Mirrors the slatestack seed (backend/scripts/seed-eaifr.ts) but expressed
 * in Pebblestack's field types: text, textarea, markdown, slug, boolean,
 * number, select, datetime, url. There's no relation/multi-relation type,
 * so cross-collection links are stored as `slug` text fields and looked up
 * in templates.
 */

return [

    // Long-form pages — About, Visit, Apply, plus the homepage if a `home`
    // slug is created. The catch-all /{slug} route resolves to page.twig.
    'pages' => [
        'label'          => 'Pages',
        'label_singular' => 'Page',
        'icon'           => 'file',
        'route'          => '/{slug}',
        'template'       => 'page.twig',
        'order_by'       => 'updated_at DESC',
        'fields' => [
            'title'            => ['type' => 'text',     'required' => true, 'label' => 'Title'],
            'slug'             => ['type' => 'slug',     'required' => true, 'label' => 'Slug',     'help' => 'URL path. Lowercase letters, numbers, dashes.'],
            'eyebrow'          => ['type' => 'text',                          'label' => 'Eyebrow label', 'help' => 'Small tracked-out label above the title.'],
            'summary'          => ['type' => 'textarea',                      'label' => 'Summary',  'help' => 'Lead paragraph under the page hero.'],
            'body'             => ['type' => 'markdown',                      'label' => 'Body',     'help' => 'Markdown supported.'],
            'meta_description' => ['type' => 'textarea',                      'label' => 'Meta description', 'help' => 'Used in <meta name="description">. ~160 chars.'],
        ],
    ],

    // News & announcements.
    'news' => [
        'label'          => 'News',
        'label_singular' => 'News item',
        'icon'           => 'edit',
        'route'          => '/news/{slug}',
        'template'       => 'news.twig',
        'list_template'  => 'news-list.twig',
        'order_by'       => 'publish_at DESC',
        'list_limit'     => 50,
        'fields' => [
            'title'        => ['type' => 'text',     'required' => true, 'label' => 'Title'],
            'slug'         => ['type' => 'slug',     'required' => true, 'label' => 'Slug'],
            'excerpt'      => ['type' => 'textarea',                     'label' => 'Excerpt',      'help' => 'Short summary for list pages and meta description.'],
            'body'         => ['type' => 'markdown',                     'label' => 'Body'],
            'published_at' => ['type' => 'datetime', 'required' => true, 'label' => 'Published on'],
            'cover_image'  => ['type' => 'url',                          'label' => 'Cover image',  'help' => 'URL from /admin/media (optional).'],
            'source_url'   => ['type' => 'url',                          'label' => 'Source URL',   'help' => 'Optional original post URL.'],
            'tag'          => ['type' => 'select',                       'label' => 'Tag',
                'options' => ['research', 'admissions', 'events', 'announcement', 'partnership']],
        ],
    ],

    // Conferences, schools, workshops, colloquia, seminars.
    'events' => [
        'label'          => 'Events',
        'label_singular' => 'Event',
        'icon'           => 'edit',
        'route'          => '/events/{slug}',
        'template'       => 'event.twig',
        'list_template'  => 'event-list.twig',
        'order_by'       => 'publish_at DESC',
        'list_limit'     => 100,
        'fields' => [
            'title'            => ['type' => 'text',     'required' => true, 'label' => 'Title'],
            'slug'             => ['type' => 'slug',     'required' => true, 'label' => 'Slug'],
            'summary'          => ['type' => 'textarea',                     'label' => 'Summary'],
            'body'             => ['type' => 'markdown',                     'label' => 'Body'],
            'start_date'       => ['type' => 'datetime', 'required' => true, 'label' => 'Start date'],
            'end_date'         => ['type' => 'datetime',                     'label' => 'End date'],
            'location'         => ['type' => 'text',                         'label' => 'Location'],
            'kind'             => ['type' => 'select',   'required' => true, 'label' => 'Type',
                'options' => ['school', 'conference', 'workshop', 'colloquium', 'seminar']],
            'cover_image'      => ['type' => 'url',                          'label' => 'Cover image'],
            'registration_url' => ['type' => 'url',                          'label' => 'Registration URL'],
        ],
    ],

    // Three (or two) research programmes.
    'research_areas' => [
        'label'          => 'Research areas',
        'label_singular' => 'Research area',
        'icon'           => 'file',
        'route'          => '/research/{slug}',
        'template'       => 'research.twig',
        'list_template'  => 'research-list.twig',
        'order_by'       => 'created_at ASC',
        'list_limit'     => 20,
        'fields' => [
            'name'        => ['type' => 'text',     'required' => true, 'label' => 'Name'],
            'slug'        => ['type' => 'slug',     'required' => true, 'label' => 'Slug'],
            'short'       => ['type' => 'text',                         'label' => 'Short tagline', 'help' => 'e.g. "Volcanology & Seismology".'],
            'overview'    => ['type' => 'markdown',                     'label' => 'Overview'],
            'cover_image' => ['type' => 'url',                          'label' => 'Cover image'],
            'accent'      => ['type' => 'select',                       'label' => 'Accent color',
                'options' => ['teal', 'navy', 'forest', 'rust']],
        ],
    ],

    // Faculty, postdocs, students, staff.
    'people' => [
        'label'          => 'People',
        'label_singular' => 'Person',
        'icon'           => 'file',
        'route'          => '/people/{slug}',
        'template'       => 'person.twig',
        'list_template'  => 'people-list.twig',
        'order_by'       => 'slug ASC',
        'list_limit'     => 200,
        'fields' => [
            'full_name'   => ['type' => 'text',     'required' => true, 'label' => 'Full name'],
            'slug'        => ['type' => 'slug',     'required' => true, 'label' => 'Slug'],
            'role'        => ['type' => 'select',                       'label' => 'Role',
                'options' => ['director', 'faculty', 'postdoc', 'phd-student', 'msc-student', 'postgraduate-student', 'alumni', 'staff', 'visiting-lecturer', 'former-staff', 'affiliate']],
            'title'       => ['type' => 'text',                         'label' => 'Title / Position'],
            'affiliation' => ['type' => 'text',                         'label' => 'Affiliation'],
            'email'       => ['type' => 'text',                         'label' => 'Email'],
            'bio'         => ['type' => 'markdown',                     'label' => 'Biography'],
            'publications_body' => ['type' => 'markdown',               'label' => 'Personal publication list', 'help' => 'Optional full bibliography. When present, replaces the automatically linked publication list on this profile.'],
            'photo'       => ['type' => 'url',                          'label' => 'Portrait', 'help' => 'URL from /admin/media.'],
            'area_slug'   => ['type' => 'text',                         'label' => 'Research area slug', 'help' => 'Slug of a research area (e.g. "geophysics").'],
        ],
    ],

    // Peer-reviewed papers.
    'publications' => [
        'label'          => 'Publications',
        'label_singular' => 'Publication',
        'icon'           => 'file',
        'route'          => '/publications/{slug}',
        'template'       => 'publication.twig',
        'list_template'  => 'publications-list.twig',
        'order_by'       => 'publish_at DESC',
        'list_limit'     => 500,
        'fields' => [
            'title'     => ['type' => 'text',   'required' => true, 'label' => 'Title'],
            'slug'      => ['type' => 'slug',   'required' => true, 'label' => 'Slug'],
            'authors'   => ['type' => 'text',                       'label' => 'Authors'],
            'year'      => ['type' => 'number',                     'label' => 'Year'],
            'venue'     => ['type' => 'text',                       'label' => 'Venue'],
            'doi'       => ['type' => 'text',                       'label' => 'DOI'],
            'url'       => ['type' => 'url',                        'label' => 'URL'],
            'area_slug' => ['type' => 'text',                       'label' => 'Research area slug'],
            'people_slugs' => ['type' => 'text',                    'label' => 'People slugs', 'help' => 'Comma-separated People slugs, with no spaces. Linked publications display automatically on each person profile.'],
        ],
    ],

    // Degree programmes (MSc in Physics, etc.).
    'programmes' => [
        'label'          => 'Programmes',
        'label_singular' => 'Programme',
        'icon'           => 'file',
        'route'          => '/academics/{slug}',
        'template'       => 'programme.twig',
        'list_template'  => 'programmes-list.twig',
        'order_by'       => 'created_at ASC',
        'list_limit'     => 20,
        'fields' => [
            'name'                => ['type' => 'text',     'required' => true, 'label' => 'Name'],
            'slug'                => ['type' => 'slug',     'required' => true, 'label' => 'Slug'],
            'summary'             => ['type' => 'textarea',                     'label' => 'Summary'],
            'body'                => ['type' => 'markdown',                     'label' => 'Body'],
            'duration'            => ['type' => 'text',                         'label' => 'Duration'],
            'application_window'  => ['type' => 'text',                         'label' => 'Application window'],
            'apply_url'           => ['type' => 'url',                          'label' => 'Application URL'],
            'cover_image'         => ['type' => 'url',                          'label' => 'Cover image'],
        ],
    ],

    // Supporting institutions. No public route — partners are surfaced as
    // logo strips on the homepage and footer, not as their own pages.
    'partners' => [
        'label'          => 'Partners',
        'label_singular' => 'Partner',
        'icon'           => 'file',
        'order_by'       => 'updated_at DESC',
        'fields' => [
            'name'  => ['type' => 'text',   'required' => true, 'label' => 'Name'],
            'slug'  => ['type' => 'slug',   'required' => true, 'label' => 'Slug'],
            'logo'  => ['type' => 'url',                        'label' => 'Logo URL'],
            'url'   => ['type' => 'url',                        'label' => 'Website URL'],
            'tier'  => ['type' => 'select',                     'label' => 'Tier',
                'options' => ['principal', 'supporter', 'funder']],
            'order' => ['type' => 'number',                     'label' => 'Display order'],
        ],
    ],

    // Public contact form. POST /forms/contact.
    'contact' => [
        'label'          => 'Contact',
        'label_singular' => 'Submission',
        'is_form'        => true,
        'fields' => [
            'name'         => ['type' => 'text',     'required' => true, 'label' => 'Name'],
            'email'        => ['type' => 'text',     'required' => true, 'label' => 'Email'],
            'affiliation'  => ['type' => 'text',                         'label' => 'Affiliation'],
            'topic'        => ['type' => 'select',                       'label' => 'Topic',
                'options' => ['General inquiry', 'Admissions', 'Research collaboration', 'Visit', 'Press']],
            'message'      => ['type' => 'textarea', 'required' => true, 'label' => 'Message'],
        ],
    ],

];
