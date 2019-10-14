
/**
 * Block manager
 */
class BlockManager
{
    constructor() {
        this.blocks = [];
    }

    /**
     * init block element with all block class
     * @param {jQuery} elem
     */
    init(elem = null) {
        _.forEach(this.blocks, function(block) {
            block.init(elem);
        });
    }

    /**
     *
     * @param {object} object
     */
    add(object) {
        if (!_.isUndefined(object.init)) {
            this.blocks.push(object);
        }
    }
}
 window.BlockManager = new BlockManager();

/**
 * block form abstract class
 */
class  Block
{
    constructor(block) {
        this.elem = {};
        this.options = {};
        this.selector = this.constructor.getSelector();
        this.cssClass = this.constructor.getCssClass();
    }

    /**
     * construct block instance
     * @param elem
     */
    static init(elem = null)
    {
        let _this = this, blocks = [];
        if (_.isEmpty(this.getSelector().init)) {
            return;
        }

        if (!elem) {
            elem = $('body')
        }

        if (elem.hasClass(this.getSelector().init)) {
            blocks.push(elem);
        } else {
            blocks = elem.find(this.getSelector().init);
        }

        let newBlock;
        blocks.each(function () {
            if (!$(this).hasClass('js-block-is-init')) {
                newBlock = new _this($(this));
                $(this).data('block', newBlock);
                $(this).addClass('js-block-is-init');
            }
        });
    }

    /**
     * @param {object} options
     */
    initOptions(options = {})
    {
        this.options = _.merge(
            this.options,
            options,
            this.elem.body.data('blockOptions') || {}
        );

        _.forEach(this.elem.body.data(), function (value, key) {
            if (_.startsWith(key, 'block') && key !== 'blockOptions') {
                key = _.lowerFirst(_.replace(key, 'block', ''));
                if (!_.isUndefined(key)) {
                    this.options[key] = value;
                }
            }
        }.bind(this));
    }

    /**
     * get css selectors of elements used in current class
     * @returns {object}
     */
    static getSelector()
    {
        return {};
    }

    /**
     * get css class of elements used in current class
     * @returns {object}
     */
    static getCssClass()
    {
        return {};
    }
}