// First we have to configure RequireJS
require.config({
	// This tells RequireJS where to find Ractive and rvc
	paths: {
		ractive1: 'PluginCompany_ContactForms/js/lib/ractive-1.0',
		rvc: 'PluginCompany_ContactForms/dfields/src/js/loaders/rvc'
	},
});

require(
	[
		"rvc!PluginCompany_ContactForms/translation/js/components/fields",
		"ractive1",
		"jquery"
	], function(
		FormFieldTranslation,
		Ractive,
		$
	) {

	'use strict';

        window.fieldTranslation = new FormFieldTranslation({
            el: 'formfieldstranslation'
        });
});
