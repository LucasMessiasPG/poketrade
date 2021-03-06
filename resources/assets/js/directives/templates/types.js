(function(){
    'use strict';

    angular
        .module('pokecard.directive')
        .directive('types',Types);

    Types.$inject = [];
    function Types() {
        return {
            restrict:'E',
            template:'<span ng-repeat="type in types track by $index"><span ng-if="type.value">{{ type.value }}</span> <i class="type"><span title="{{ type.type }}" class="img {{ type.type }}"></span></i></span>',
            scope:{
              data:'='
            },
            link: function (scope) {
                var type  = scope.data;

                scope.types = [];
                if(typeof type == 'string')
                    scope.types.push({type:type});
                else {
                    if (type.type && type.value) {
                        scope.types.push(type);
                    } else {
                        for (var i in type) {
                            if (typeof type[i] == 'string')
                                scope.types.push({type: type[i]});
                            else {
                                scope.types.push(type[i]);
                            }

                        }
                    }
                }
                scope.types.reverse();

            }
        }
    }

})();