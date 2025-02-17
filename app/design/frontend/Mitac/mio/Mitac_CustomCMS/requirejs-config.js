var config = {
    map: {
        '*': {
            searchMaps: 'Mitac_CustomCMS/js/searchMap',
            'Mitac_CustomCMS/searchMap': 'Mitac_CustomCMS/js/searchMap',
            searchAccessories:'Mitac_CustomCMS/js/searchAccessory',
            'Mitac_CustomCMS/searchAccessory': 'Mitac_CustomCMS/js/searchAccessory'
        }
    },
    paths: {
        mio_slick: 'Mitac_CustomCMS/js/slick'
    },
    shim: {
        mio_slick: {
            deps: ['jquery']
        }
    }
};