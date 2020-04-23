
var app = angular.module("queryBuilderApp", []);


app.controller("formController", function($http,$scope) {
    
//    $scope.fn_load = function () {
//    console.log("page load")
//  };
//  			$window.onload = function() {
//
//			 	alert("Angularjs call function on page load");
//
//			};
                        
                        
//   $scope.init=function()
//        {
//            console.log("Angularjs call function on page load")
//       /*
//       $http.get("http://180.151.3.101/dbtv2/progressreport/searchreportangular")
//    .then(function(response) {
//       $scope.myData = response.data;
//       console.log("----------"+ response.data);
//     });
//       */
//       
//       
//        }
       //simple call init function on controller
       //$scope.init();
    
//var a=$scope.governanace;
      //alert("sdfsdfds");
    //$scope.governanace = 'center';
    //console.log($scope.governanace);
      // $scope.onGovernanaceChange = function () {
           //console.log($scope.governanace);
           //alert($scope.governanace);
    //$scope.governanace.isChecked = false;
    
//      if($scope.governanace.checked=true){
//         //$scope.governanace=true;
//      }
           //if($scope.governanace=='state'){
//                   $http.get("/dbtv2/progressreport/searchreport", $scope.governanace).then(function (responses) { 
//                       //$scope.DesigMas = responses.data;  
//                       //$scope.Designation = $scope.DesigMas[0];  
//                    });  
               // }  
 // }
  
  
  
}); 


//function myQueryController($scope, $http) {
//$scope.login = {email: "", password: ""};
////$scope.login = $.param($scope.login);
//$scope.submit = function(){
//
//    $http({
//        method: 'POST',
//        url: '/dbtv2/progressreport/searchreport',
//        data: $scope.login,
//        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
//
//    }).
//        success(function(response) {
//            window.location.href = "/dbtv2/progressreport/searchreport";
//        }).
//        error(function(response) {
//            $scope.codeStatus = response || "Request failed";
//        });
//}
//}


//app.directive('datepicker', function () {
//    return {
//        restrict: 'C',
//        require: 'ngModel',
//         link: function (scope, element, attrs, ngModelCtrl) {
//            element.datepicker({
//                dateFormat: 'dd, MM, yy',
//                onSelect: function (date) {
//                    scope.date = date;
//                    scope.$apply();
//                }
//            });
//        }    
//    };
//});



//app.controller('DatepickerPopupDemoCtrl', function ($scope) {
//  $scope.today = function() {
//    $scope.dt = new Date();
//  };
//  $scope.today();
//
//  $scope.clear = function() {
//    $scope.dt = null;
//  };
//
//  $scope.inlineOptions = {
//    customClass: getDayClass,
//    minDate: new Date(),
//    showWeeks: true
//  };
//
//  $scope.dateOptions = {
//    dateDisabled: disabled,
//    formatYear: 'yy',
//    maxDate: new Date(2020, 5, 22),
//    minDate: new Date(),
//    startingDay: 1
//  };
//
//  // Disable weekend selection
//  function disabled(data) {
//    var date = data.date,
//      mode = data.mode;
//    return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
//  }
//
//  $scope.toggleMin = function() {
//    $scope.inlineOptions.minDate = $scope.inlineOptions.minDate ? null : new Date();
//    $scope.dateOptions.minDate = $scope.inlineOptions.minDate;
//  };
//
//  $scope.toggleMin();
//
//  $scope.open1 = function() {
//    $scope.popup1.opened = true;
//  };
//
//  $scope.open2 = function() {
//    $scope.popup2.opened = true;
//  };
//
//  $scope.setDate = function(year, month, day) {
//    $scope.dt = new Date(year, month, day);
//  };
//
//  $scope.formats = ['dd-MMMM-yyyy', 'yyyy/MM/dd', 'dd.MM.yyyy', 'shortDate'];
//  $scope.format = $scope.formats[0];
//  $scope.altInputFormats = ['M!/d!/yyyy'];
//
//  $scope.popup1 = {
//    opened: false
//  };
//
//  $scope.popup2 = {
//    opened: false
//  };
//
//  var tomorrow = new Date();
//  tomorrow.setDate(tomorrow.getDate() + 1);
//  var afterTomorrow = new Date();
//  afterTomorrow.setDate(tomorrow.getDate() + 1);
//  $scope.events = [
//    {
//      date: tomorrow,
//      status: 'full'
//    },
//    {
//      date: afterTomorrow,
//      status: 'partially'
//    }
//  ];
//
//  function getDayClass(data) {
//    var date = data.date,
//      mode = data.mode;
//    if (mode === 'day') {
//      var dayToCheck = new Date(date).setHours(0,0,0,0);
//
//      for (var i = 0; i < $scope.events.length; i++) {
//        var currentDay = new Date($scope.events[i].date).setHours(0,0,0,0);
//
//        if (dayToCheck === currentDay) {
//          return $scope.events[i].status;
//        }
//      }
//    }
//
//    return '';
//  }
//});


app.controller("resultController", function($scope, $http) {
//        $scope.display_data = function() {
//            $http.get("progressreport/searchreportang")
//                .success(function(data) {
//                    $scope.names = data;
//                });
//        }
    });





