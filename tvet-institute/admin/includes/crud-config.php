<?php
/**
 * Configuration-driven CRUD entities.
 * Each entity maps to a table and a set of fields; admin/crud.php renders
 * the list + create/edit form generically from this config so that adding
 * a new simple content type does not require writing new PHP pages.
 *
 * Field types: text, textarea, select, checkbox, number, date, datetime-local, image, file
 */
function crud_entities(): array
{
    return [
        'departments' => [
            'label' => 'Departments',
            'table' => 'departments',
            'order_by' => 'sort_order ASC',
            'title_field' => 'name',
            'list_columns' => ['name', 'slug', 'sort_order'],
            'fields' => [
                'name' => ['label' => 'Department Name', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Slug', 'type' => 'text', 'auto_slug_from' => 'name'],
                'icon' => ['label' => 'Bootstrap Icon class', 'type' => 'text', 'help' => 'e.g. bi-cpu, bi-briefcase (see icons.getbootstrap.com)'],
                'description' => ['label' => 'Short description', 'type' => 'textarea'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
            ],
        ],
        'staff' => [
            'label' => 'Board & Trainers',
            'table' => 'staff',
            'order_by' => 'sort_order ASC',
            'title_field' => 'full_name',
            'upload_folder' => 'staff',
            'list_columns' => ['photo', 'full_name', 'title', 'category', 'is_published'],
            'fields' => [
                'full_name' => ['label' => 'Full Name', 'type' => 'text', 'required' => true],
                'title' => ['label' => 'Title / Role', 'type' => 'text'],
                'category' => ['label' => 'Category', 'type' => 'select', 'options' => ['leadership' => 'Leadership (Head of Institute)', 'board' => 'Board Member', 'trainer' => 'Trainer']],
                'bio' => ['label' => 'Bio / Message', 'type' => 'textarea'],
                'photo' => ['label' => 'Photo', 'type' => 'image'],
                'email' => ['label' => 'Email', 'type' => 'text'],
                'phone' => ['label' => 'Phone', 'type' => 'text'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'news' => [
            'label' => 'News',
            'table' => 'news',
            'order_by' => 'published_at DESC',
            'title_field' => 'title',
            'upload_folder' => 'news',
            'list_columns' => ['image', 'title', 'category', 'published_at', 'is_published'],
            'fields' => [
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Slug', 'type' => 'text', 'auto_slug_from' => 'title'],
                'category' => ['label' => 'Category', 'type' => 'text', 'default' => 'News'],
                'excerpt' => ['label' => 'Excerpt (short summary)', 'type' => 'textarea'],
                'body' => ['label' => 'Full Article', 'type' => 'textarea', 'rows' => 10],
                'image' => ['label' => 'Cover Image', 'type' => 'image'],
                'published_at' => ['label' => 'Publish Date', 'type' => 'datetime-local', 'default_now' => true],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'events' => [
            'label' => 'Events',
            'table' => 'events',
            'order_by' => 'start_date DESC',
            'title_field' => 'title',
            'upload_folder' => 'events',
            'list_columns' => ['image', 'title', 'location', 'start_date', 'is_published'],
            'fields' => [
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'slug' => ['label' => 'Slug', 'type' => 'text', 'auto_slug_from' => 'title'],
                'description' => ['label' => 'Description', 'type' => 'textarea'],
                'location' => ['label' => 'Location', 'type' => 'text'],
                'start_date' => ['label' => 'Start Date & Time', 'type' => 'datetime-local', 'required' => true],
                'end_date' => ['label' => 'End Date & Time', 'type' => 'datetime-local'],
                'image' => ['label' => 'Image', 'type' => 'image'],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'sliders' => [
            'label' => 'Homepage Sliders',
            'table' => 'sliders',
            'order_by' => 'sort_order ASC',
            'title_field' => 'title',
            'upload_folder' => 'sliders',
            'list_columns' => ['image', 'title', 'sort_order', 'is_published'],
            'fields' => [
                'title' => ['label' => 'Headline', 'type' => 'text', 'required' => true],
                'subtitle' => ['label' => 'Sub-headline', 'type' => 'textarea'],
                'image' => ['label' => 'Background Image', 'type' => 'image'],
                'cta_text' => ['label' => 'Button Text', 'type' => 'text', 'default' => 'Apply Now'],
                'cta_link' => ['label' => 'Button Link', 'type' => 'text', 'default' => 'apply.php'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'gallery' => [
            'label' => 'Gallery',
            'table' => 'gallery',
            'order_by' => 'sort_order DESC',
            'title_field' => 'title',
            'upload_folder' => 'gallery',
            'list_columns' => ['image', 'title', 'category', 'is_published'],
            'fields' => [
                'title' => ['label' => 'Caption', 'type' => 'text'],
                'image' => ['label' => 'Image', 'type' => 'image', 'required' => true],
                'category' => ['label' => 'Category', 'type' => 'text', 'default' => 'Campus Life'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'testimonials' => [
            'label' => 'Testimonials',
            'table' => 'testimonials',
            'order_by' => 'sort_order ASC',
            'title_field' => 'full_name',
            'upload_folder' => 'testimonials',
            'list_columns' => ['photo', 'full_name', 'programme', 'is_published'],
            'fields' => [
                'full_name' => ['label' => 'Graduate Name', 'type' => 'text', 'required' => true],
                'programme' => ['label' => 'Programme Studied', 'type' => 'text'],
                'photo' => ['label' => 'Photo', 'type' => 'image'],
                'quote' => ['label' => 'Quote', 'type' => 'textarea', 'required' => true],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'downloads' => [
            'label' => 'Downloads',
            'table' => 'downloads',
            'order_by' => 'category ASC',
            'title_field' => 'title',
            'upload_folder' => 'downloads',
            'list_columns' => ['title', 'category', 'file', 'is_published'],
            'fields' => [
                'title' => ['label' => 'Title', 'type' => 'text', 'required' => true],
                'description' => ['label' => 'Description', 'type' => 'text'],
                'category' => ['label' => 'Category', 'type' => 'text', 'default' => 'General', 'help' => 'e.g. Prospectus, Application Forms, Fee Structures'],
                'file' => ['label' => 'File (PDF/DOC)', 'type' => 'file', 'allowed' => ['pdf','doc','docx','xls','xlsx']],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'faqs' => [
            'label' => 'FAQs',
            'table' => 'faqs',
            'order_by' => 'sort_order ASC',
            'title_field' => 'question',
            'list_columns' => ['question', 'sort_order', 'is_published'],
            'fields' => [
                'question' => ['label' => 'Question', 'type' => 'text', 'required' => true],
                'answer' => ['label' => 'Answer', 'type' => 'textarea', 'required' => true],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
                'is_published' => ['label' => 'Published', 'type' => 'checkbox', 'default' => 1],
            ],
        ],
        'partners' => [
            'label' => 'Partners & Accreditation',
            'table' => 'partners',
            'order_by' => 'sort_order ASC',
            'title_field' => 'name',
            'upload_folder' => 'site',
            'list_columns' => ['logo', 'name', 'url', 'sort_order'],
            'fields' => [
                'name' => ['label' => 'Name', 'type' => 'text', 'required' => true],
                'logo' => ['label' => 'Logo', 'type' => 'image'],
                'url' => ['label' => 'Website URL', 'type' => 'text'],
                'sort_order' => ['label' => 'Sort Order', 'type' => 'number', 'default' => 0],
            ],
        ],
    ];
}
