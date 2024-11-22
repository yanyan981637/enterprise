// First we have to configure RequireJS
require.config({
	// This tells RequireJS where to find Ractive and rvc
	paths: {
		ractive: 'PluginCompany_ContactForms/dfields/src/js/lib/ractive-legacy',
        // jquery: '../bower_components/jquery/dist/jquery.min',
        select2: 'PluginCompany_ContactForms/dfields/src/bower_components/select2/dist/js/select2.full.min',
		rvc: 'PluginCompany_ContactForms/dfields/src/js/loaders/rvc'
	},
});

// Now we've configured RequireJS, we can load our dependencies and start

/*reqlist*/require(["rvc!PluginCompany_ContactForms/dfields/src/js/components/dependencies","rvc!PluginCompany_ContactForms/dfields/src/js/components/dfields","rvc!PluginCompany_ContactForms/dfields/src/js/components/form/dependentFields","rvc!PluginCompany_ContactForms/dfields/src/js/components/form/select2","rvc!PluginCompany_ContactForms/dfields/src/js/components/form/select2label","ractive","PluginCompany_ContactForms/dfields/src/js/decorators/RactiveSelect2","PluginCompany_ContactForms/dfields/src/js/decorators/RactiveSortable","jquery"], function( Dependencies, Dfields, Dependentfields, Select2, Select2label, Ractive, RactiveSelect2, RactiveSortable, $ ) {

	'use strict';

    Ractive.decorators.sortable = RactiveSortable;
    Ractive.decorators.sortable.targetClass = 'placeholder';

	window.dfields = new Dfields({
		el: 'dfields'
	});
});
