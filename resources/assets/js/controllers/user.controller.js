(function(){
    'use strick';


    angular
        .module('pokecard.controller')
        .controller('UserController', UserController)

    UserController.$inject = [];
    function UserController() {
        var user = this;
        console.log('UserController');
    }

})();