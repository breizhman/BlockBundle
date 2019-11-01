/**
 * BlockCollectionForm class
 */
class BlockCollectionForm extends Block {

    /**
     * @param {jQuery} block main element of class
     */
    constructor(block) {
        super(block);

        this.elem = {
            body : block,
            list: null,
            loader: null,
            wrapper: null,
            action: {
                create: null,
                remove: null
            },
            input: {
                order: null,
            },
            templates: {},
        };

        this.elem.loader = this.elem.body.find(this.selector.loader);
        this.elem.wrapper = this.elem.body.find(this.selector.wrapper);

        this.elem.list = this.elem.body.find(this.selector.list);
        this.elem.action.create = this.elem.body.find(this.selector.action.create);
        this.elem.input.order = this.elem.body.find(this.selector.input.order);

        this.initTemplates();

        this.initBlockOrder();

        this.bindEvents();

        this.isLoaded();
    }

    /**
     * @inheritDoc
     */
    static getSelector() {
        return {
            init: '.js-block-collection',
            block: '.js-block-form',
            list: '.js-block-collection-list',
            loader: '.js-block-collection-loader',
            wrapper: '.js-block-collection-wrapper',
            action: {
                create: '.js-block-collection-action-create',
                remove: '.js-block-collection-action-remove'
            },
            input: {
                order: '.js-block-collection-order'
            },
            sortable: {
                cancel: '.' + BlockCollectionForm.getCssClass().sortable.cancel,
                items: "> .js-block-sortable-item",
            },
            template: '.js-block-collection-template',
        };
    }

    /**
     * @inheritDoc
     */
    static getCssClass() {
        return {
            sortable: {
                cancel: 'js-block-collection-sortable-cancel',
            }
        };
    }

    /**
     * bind block events
     */
    bindEvents() {
        let _this = this;
        this.elem.action.create.on('click', function(e) {
            e.preventDefault();
            _this.addBlock($(this).data('blockName'));
        });

        this.elem.list.sortable({
            placeholder: "placeholder",
            cancel: this.selector.sortable.cancel,
            items: this.selector.sortable.items,
            create: function( event, ui ) {
                _this.elem.list.find('> *').wrapInner( "<div class='" + BlockCollectionForm.getCssClass().sortable.cancel + "'></div>" );
            } ,
            start: function( event, ui ) {
                ui.placeholder.height(ui.item.outerHeight());
            },
            stop: function( event, ui ) {
                // remove style add by sortable plugin
                $(ui.item)
                    .css('top', '')
                    .css('left', '')
                ;
            },
            change: function( event, ui ) {
                ui.placeholder.height(ui.item.outerHeight());

                setTimeout(_this.updateBlockOrder(), 1);
            },
            update: function() {
                _this.updateBlockOrder();
            }
        });

        this.elem.list.find(this.selector.block).each(function() {
            _this.bindItemEvents($(this));
        });
    }

    /**
     * bind block items events
     * @param {jQuery} block
     */
    bindItemEvents(block) {
        let _this = this;

        block.find(this.selector.action.remove).on('click', function (e) {
            e.preventDefault();
            _this.removeBlock(block);
        });

        window.BlockManager.init(block);
        window.App.initAll(block);
    }

    /**
     * @param {string} blockName
     */
    addBlock(blockName) {
        if (_.isUndefined(this.elem.templates[blockName])) {
            return;
        }

        // replace id and name of prototype
        let block = FormUtils.parseTemplate(
            this.elem.templates[blockName],
            this.elem.list.find(this.selector.block).length
        );

        // create a new list element and add it to the list
        this.elem.list.append(block);

        // add wrapper to sortable
        block.wrapInner( "<div class='" + BlockCollectionForm.getCssClass().sortable.cancel + "'></div>" );

        this.bindItemEvents(block);

        this.updateBlockOrder();

        this.refreshSortable();
    }

    /**
     * @param {jQuery} block
     */
    removeBlock(block) {
        let id = block.attr('id');
        if (id) {
            this.elem.list.find('#' + id).remove();

            this.updateBlockOrder();
            this.refreshSortable();
        }
    }

    updateBlockOrder() {
        let order = [];
        this.elem.list.find(this.selector.block).each(function() {
            order.push({
                'name' : $(this).data('blockName'),
                'pos' : _.last(_.split($(this).attr('id'), '_'))
            });
        });

        this.elem.input.order.val(JSON.stringify(order));
    }

    initBlockOrder() {
        let _this = this,
            currentBlock,
            order = JSON.parse(this.elem.input.order.val())
        ;

        _.forEach(order, function(data) {
            data = _.isString(data) ? JSON.parse(data) : data;
            if (!_.isUndefined(data.name) && !_.isUndefined(data.pos)) {

                // get block by name and pos
                let block = _this.elem.list.find(_this.selector.block + '[data-block-name="' + data.name + '"]')[data.pos];
                if (!_.isUndefined(block)) {

                    if (_.isUndefined(currentBlock)) {
                        _this.elem.list.prepend(block);
                    } else {
                        $(block).insertAfter(currentBlock);
                    }

                    currentBlock = block;
                }
            }
        });

        this.updateBlockOrder();
    }

    isLoaded() {
        this.elem.loader.addClass('hide');
        this.elem.wrapper.removeClass('hide');
    }

    refreshSortable() {
        this.elem.body.find('.ui-sortable').sortable( "refresh" );
    }

    initTemplates() {
        let _this = this;
        this.elem.body.find(this.selector.template).each(function() {
            _this.elem.templates[$(this).data('blockName')] = $(this);
        });
    }
}

 window.BlockManager.add(BlockCollectionForm);

$( document ).ready(function() {
    BlockCollectionForm.init();
});
