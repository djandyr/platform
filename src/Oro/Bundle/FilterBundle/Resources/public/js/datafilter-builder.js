/*jshint browser:true*/
/*jslint nomen: true*/
/*global define, require*/
define(['jquery', 'underscore', 'oro/tools', 'oro/mediator', 'orofilter/js/map-filter-module-name',
    'oro/datafilter/collection-filters-manager'],
function ($, _, tools,  mediator, mapFilterModuleName, FiltersManager) {
    'use strict';

    var initialized = false,
        methods = {
            initBuilder: function () {
                this.metadata = _.extend({filters: {}}, this.$el.data('metadata'));
                this.modules = {};
                methods.collectModules.call(this);
                tools.loadModules(this.modules, _.bind(methods.build, this));
            },

            /**
             * Collects required modules
             */
            collectModules: function () {
                var modules = this.modules;
                _.each(this.metadata.filters || {}, function (filter) {
                     var type = filter.type;
                     modules[type] = mapFilterModuleName(type);
                });
            },

            build: function () {
                var options = methods.combineOptions.call(this);
                options.collection = this.collection;
                var filtersList = new FiltersManager(options);
                this.$el.prepend(filtersList.render().$el);
                mediator.trigger('datagrid_filters:rendered', this.collection);
                if (this.collection.length === 0) {
                    filtersList.$el.hide();
                }
            },

            /**
             * Process metadata and combines options for filters
             *
             * @returns {Object}
             */
            combineOptions: function () {
                var filters= {},
                    modules = this.modules,
                    collection = this.collection;
                _.each(this.metadata.filters, function (options) {
                    if (_.has(options, 'name') && _.has(options, 'type')) {
                        // @TODO pass collection only for specific filters
                        if (options.type == 'selectrow') {
                            options.collection = collection
                        }
                        var Filter = modules[options.type].extend(options);
                        filters[options.name] = new Filter();
                    }
                });
                return {filters: filters};
            }
        },
        initHandler = function (collection, $el) {
            methods.initBuilder.call({$el: $el, collection: collection});
            initialized = true;
        };

    return {
        init: function () {
            initialized = false;

            mediator.once('datagrid_collection_set_after', initHandler);
            mediator.once('hash_navigation_request:start', function() {
                if (!initialized) {
                    mediator.off('datagrid_collection_set_after', initHandler);
                }
            });
        }
    };
});
