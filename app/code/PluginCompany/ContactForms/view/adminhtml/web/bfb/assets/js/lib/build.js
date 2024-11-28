({
  name: "../main",
  out: "../main-built.js"
// ,optimize: "none"
  , shim: {
    'backbone': {
      deps: ['underscore', 'jquery2'],
      exports: 'Backbone'
    },
    'underscore': {
      exports: '_'
    },
    'bootstrap': {
      deps: ['jquery2']
      // exports: '$.fn.popover'
    }
  }
  , paths: {
    app         : ".."
    , collections : "../collections"
    , data        : "../data"
    , models      : "../models"
    , helper      : "../helper"
    , templates   : "../templates"
    , views       : "../views"
  }
})
