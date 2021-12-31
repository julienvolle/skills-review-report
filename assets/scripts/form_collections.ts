import * as $ from 'jquery';
import Sortable from 'sortablejs';

/** @see https://symfony.com/doc/current/form/form_collections.html */

const config = {
    'class': {
        'list': {
            'root': '.form-collection',
            'item': '.form-collection-item',
        },
        'button': {
            'add': '.form-collection-button-add',
            'del': '.form-collection-button-del'
        }
    },
    'data_attribute': {
        'id':             'data-form-collection-id',
        'container':      'data-form-collection-container',
        'prototype':      'data-form-collection-prototype',
        'prototype_name': 'data-form-collection-prototype-name'
    }
};

$(document).ready(function() {
    $(config.class.list.root).each(function() {
        initCollection($(this));
    });
});

function initCollection(collection) {
    collection.find(config.class.list.item).each(function() {
        initButtonOnItem($(this));
    });
    let tagName = $(collection.data(getDataKey(config.data_attribute.container))).prop('tagName');
    collection.data('index', collection.find(tagName.toLowerCase()).length);
    $('*[' + config.data_attribute.id + '="' + collection.attr('id') + '"]').on('click', function(e) {
        let collection = $('#' + $(e.currentTarget).data(getDataKey(config.data_attribute.id)));
        addItemToCollection(collection);
        refreshCollectionItemPriority(collection.attr('id'));
    });
    initSortable(collection);
}

function addItemToCollection(collection) {
    let container = collection.data(getDataKey(config.data_attribute.container));
    let prototype = collection.data(getDataKey(config.data_attribute.prototype));
    let form = prototype.replace(
        new RegExp(collection.data(getDataKey(config.data_attribute.prototype_name)),'g'),
        collection.data('index')
    );
    let item = $(container).append(form);
    initButtonOnItem(item);
    collection.append(item);
    collection.data('index', collection.data('index') + 1);
    item.find(config.class.list.root).each(function() {
        initCollection($(this));
    });
}

function initButtonOnItem(item) {
    item.find(config.class.button.del).first().on('click', function() {
        let collection = item.closest('ul');
        item.remove();
        refreshCollectionItemPriority(collection.attr('id'));
    });
}

function getDataKey(dataAttribute) {
    return dataAttribute
        .replace(/^data-/g, '')
        .replace(/-/g, ' ')
        .replace(/\W+(.)/g, function(word, index) {
            return index === 0 ? word.toLowerCase() : word.toUpperCase();
        })
        .replace(/\s+/g, '');
}

function initSortable(collection) {
    Sortable.create(document.getElementById(collection.attr('id')), {
        animation: 200,
        sort: true,
        handle: ".handle",
        onEnd: function () {
            refreshCollectionItemPriority(collection.attr('id'));
        },
    });
}

function refreshCollectionItemPriority(collectionId): void
{
    let collection = document.getElementById(collectionId);
    $(collection).find('li').each(function(index) {
        $(this).find('[data-sortable="priority"]').attr('value', index + 1);
    });
}
