import './bootstrap';
import router from './routes';
import store from './store';

require('bootstrap/dist/css/bootstrap.css');
require('admin-lte/dist/css/AdminLTE.css');
require('admin-lte/dist/css/skins/skin-blue.css');
require('verte/dist/verte.css');
require('chosen-js/chosen.css');
require('chosen-bootstrap-theme/dist/chosen-bootstrap-theme.css');
require('eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css');
require('placeholder-loading/src/scss/placeholder-loading.scss');
require('../sass/app.scss');

// Vue.component('vuetable', require('vuetable-2/src/components/Vuetable.vue'));
// Vue.component('vuetable-pagination', require('vuetable-2/src/components/VuetablePagination.vue'));
Vue.component('verte', require('verte/dist/verte'));

// These components are being used directly in twig templates
Vue.component('sidebar', require('./components/Sidebar.vue').default);
Vue.component('userbar', require('./components/Userbar.vue').default);
Vue.component('exceptionpane', require('./components/ExceptionPane.vue').default);

/** Are you using these components below? I can't find any use of them throughout the application **/
Vue.component('optionlistraw', require('./components/OptionListRaw.vue').default);
Vue.component('arraytableform', require('./components/ArrayTableForm.vue').default);
/*****/


Vue.filter('dateFormat', require('./filters/dateFormat'));
Vue.filter('dateTimeFormat', require('./filters/dateTimeFormat'));
Vue.filter('dateTimeMonthFormat', require('./filters/dateTimeMonthFormat'));
Vue.filter('numberFormat', require('./filters/numberFormat'));
Vue.filter('currencyFormat', require('./filters/currencyFormat'));
Vue.filter('statusFormat', require('./filters/statusFormat'));

Vue.directive('chosen', {
    inserted: function(el, binding, vnode) {
        jQuery(el).chosen({
            disable_search_threshold: 10,
            width: '100%' // Needed for fields that are initially hidden
        }).change(function(event, change) {
            vnode.data.on.change(event, $(el).val());
        });
    },
    componentUpdated: function(el, binding) {
        jQuery(el).trigger("chosen:updated");
    }
});

Vue.directive('tooltip', {
    inserted: function(el, binding, vnode) {
        jQuery(el).tooltip({
            placement: binding.value
        });
    }
});

Vue.directive('datepicker', {
    inserted: function(el, binding, vnode) {
        binding.value = binding.value || {};
        let format = binding.value.format || 'YYYY-MM-DD';
        let tz = binding.value.tz || null;
        let value = moment.tz($(el).val(), format, tz);

        jQuery(el).datetimepicker({
            format: format
        }).on('dp.change', function (e) {
            vnode.data.on.change(e, e.date);
        });
        $(el).data('DateTimePicker').date(value);
    },
    componentUpdated: function(el, binding) {
        binding.value = binding.value || {};
        let format = binding.value.format || 'YYYY-MM-DD';
        let tz = binding.value.tz || null;
        let value = moment.tz(el.value, format, tz);
        jQuery(el).data("DateTimePicker").date(value);
    }
});

window.App = new Vue({
    el: '#app',
    data: {
        exceptions: []
    },
    created() {
        this.$store.dispatch('loadCurrentUser')
    },
    router,
    store
});
