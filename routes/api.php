<?php

use App\Http\Controllers\ActiveUsersController;
use App\Http\Controllers\Auth\EmployeeAuthController;
use App\Http\Controllers\Auth\GarageAuthController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\FavGarageController;
use App\Http\Controllers\FloorsController;
use App\Http\Controllers\GarageEmployeesController;
use App\Http\Controllers\GaragesController;
use App\Http\Controllers\GarageSubscriptionsController;
use App\Http\Controllers\ParkingsController;
use App\Http\Controllers\PayFeesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSubscriptionsController;
use App\Http\Controllers\WParksController;
use App\Http\Controllers\WParksCustomerController;
use App\Models\garages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::group([ 'prefix' => 'auth'], function () {
    //user
    Route::post('user-login', [UserAuthController::class, 'user_login']);
    Route::post('user-new-account', [UserAuthController::class, 'user_new_account']);
    //garage
    Route::post('garage-new-account', [GarageAuthController::class, 'garage_new_account']);
    Route::post('garage-login', [GarageAuthController::class, 'garage_login']);
    Route::post('employee-new-account', [GarageAuthController::class, 'employee_new_account']);
    //employee
    Route::post('employee-login', [EmployeeAuthController::class, 'employee_login']);



    Route::get('/garages/search', [GaragesController::class, 'search']);//dd
//    Route::get('/garages', [GaragesController::class, 'index']);
    Route::get('/garages/{garage_id}', [GaragesController::class, 'show']);//dd
    Route::get('/{user_id}/favorite_garages', [FavGarageController::class, 'index']);//dd

    Route::get('/availableAndNonAvailabelGarages', [GaragesController::class, 'availableAndNonAvailabelGarages']);//dd
    Route::get('/user-subscriptions/{id}', [UserSubscriptionsController::class, 'getUserSubscriptions']);//dd
    Route::get('/garages/{id}/parking', [ParkingsController::class, 'showParkingForGarages']);//dd
    Route::get('/garages/{garageId}/parking/{parkingId}', [ParkingsController::class, 'showInfoParkingForGarages']);//dd //show information for specific parking

    Route::post('/delete-reserve-parking/{reservationId}', [ReservationsController::class, 'deleteReservation']);/////////
    Route::get('/show-reservation-parking/{userId}', [ReservationsController::class, 'getUserReservations']);//dd
    Route::get('/getArchivedReservationsForUser/{userId}', [ReservationsController::class, 'getArchivedReservationsForUser']);//dd


    //add status
    Route::get('/add-status', [StatusController::class, 'addStatus']);
    Route::post('/subscriptions', [SubscriptionsController::class, 'createSubscriptionType']);
    Route::post('/garage-subscriptions', [GarageSubscriptionsController::class, 'addGarageSubscription']);
    Route::post('/delete-garage-subscriptions/{id}', [GarageSubscriptionsController::class, 'deleteGarageSubscription']);
    Route::post('/edit-garage-subscriptions/{id}', [GarageSubscriptionsController::class, 'editGarageSubscription']);



//    M_E
    Route::get('/customer/search', [GarageEmployeesController::class, 'searchByNameForCustomer']);
    Route::get('/user/search', [GarageEmployeesController::class, 'searchByNameForUser']);
    Route::get('/user-garage-subscription/{garageId}', [SubscriptionsController::class, 'getUsersSubscribedInGarage']);
//    Route::get('/garages/{id}/parking', [ParkingsController::class, 'showParkingForGarages']);
    Route::get('/getWParksForGarage/{garageId}', [WParksController::class, 'getWParksForGarage']);
//    Route::get('showAccountEmployees/{id}', [GarageEmployeesController::class,'showAccountEmployees']);
    Route::get('/getActiveUserForGarage/{garageId}', [WParksController::class, 'getActiveUserForGarage']);
    Route::get('/active_user/search', [GarageEmployeesController::class, 'searchByNameForActiveUser']);

    //accounter
//    Route::get('get_all_user_info', [UserController::class, 'get_all_user_info']);//done

    Route::group(['middleware'=>'auth:user'],function (){
        //user
        Route::post('user-logout', [UserAuthController::class, 'user_logout']);//dd
        Route::get('get_user_info', [UserController::class, 'get_user_info']);//done
        Route::post('update_user_info', [UserController::class, 'update_user_info']);//dd
        Route::get('get_user_cars', [CarsController::class, 'get_user_cars']);//dd
        Route::post('delete_user_car/{car}', [CarsController::class, 'delete_user_car']);//dd
        Route::post('add_user_car', [CarsController::class, 'add_user_car']);//dd
        Route::post('update_user_car/{car_id}', [CarsController::class, 'update_user_car']);//dd
        Route::post('garages/addToFavorites', [FavGarageController::class, 'addToFavorites']);//dd
        Route::post('garages/removeFromFavorites', [FavGarageController::class, 'removeFromFavorites']);//dd
        Route::get('get_required_services', [UserController::class, 'get_required_services']);//dd
        Route::get('showGarageServices/{id}', [ServicesController::class,'showGarageServices']);//dd
        Route::post('/reserve-parking', [ReservationsController::class, 'reserveParking']);//dd
        Route::post('/edit-reserve-parking/{reservationId}', [ReservationsController::class, 'updateReservation']);//dd
        Route::post('/subscriptions_with_garage', [UserSubscriptionsController::class, 'subscriptions_with_garage']);//dd
        Route::post('/update_subscriptions_with_garage', [UserSubscriptionsController::class, 'update_subscriptions_with_garage']);//dd
        Route::post('/users/request-services', [UserController::class, 'requestServices']);//dd
        Route::get('/getWParksForUser', [WParksController::class, 'getWParksForUser']);//dd
        Route::get('/showGarageSubscriptions/{garageId}', [GarageSubscriptionsController::class, 'showGarageSubscriptions']);//dd
    });

    Route::group(['middleware'=>'auth.garage'],function (){
        //garage
        Route::post('garage-logout', [GarageAuthController::class, 'garage_logout']);
        Route::post('employee-new-account', [GarageAuthController::class, 'employee_new_account']);
        /////////////////////////////////
        //garage
        Route::post('garage-logout', [GarageAuthController::class, 'garage_logout']);
        Route::post('employee-new-account', [GarageAuthController::class, 'employee_new_account']);
        Route::post('updateAccountInfo', [GaragesController::class,'updateAccountInfo']);
        Route::get('getAccountInfo', [GaragesController::class,'getAccountInfo']);
        Route::get('getAllFloorsAndAttributes', [GaragesController::class,'getAllFloorsAndAttributes']);
        Route::post('addparking', [ParkingsController::class,'addparking']);
        Route::post('updateParkingInfo/{id}', [ParkingsController::class,'updateParkingInfo']);
        Route::post('updateParkingInfo/{id}', [ParkingsController::class,'updateParkingInfo']);
        Route::delete('removeParking/{id}', [ParkingsController::class,'removeParking']);
        Route::get('showMyEmployees', [GarageEmployeesController::class,'showMyEmployees']);
        Route::get('showAccountEmployees/{id}', [GarageEmployeesController::class,'showAccountEmployees']);
        Route::post('updateEmployeeInfo/{id}', [GarageEmployeesController::class,'updateEmployeeInfo']);
        Route::delete('removeEmployee/{id}', [GarageEmployeesController::class,'removeEmployee']);
        Route::get('searchByNameforEmployee/{name}', [GarageEmployeesController::class,'searchByNameforEmployee']);
        Route::get('getAllFloors', [FloorsController::class,'getAllFloors']);
        Route::post('updateFloorInfo/{id}', [FloorsController::class,'updateFloorInfo']);
        Route::delete('removeFloor/{id}', [FloorsController::class,'removeFloor']);
        Route::get('showMyServices', [ServicesController::class,'showMyServices']);
        Route::delete('removeService/{id}', [ServicesController::class,'removeService']);
        Route::post('addSubscription', [GarageSubscriptionsController::class,'addSubscription']);
        Route::post('addservice', [ServicesController::class,'addservice']);
        Route::post('updateServiceInfo/{id}', [ServicesController::class,'updateServiceInfo']);
        Route::get('showSubscriptions', [SubscriptionsController::class,'showSubscriptions']);
        Route::get('showMySubscription', [GarageSubscriptionsController::class,'showMySubscription']);
        Route::post('updateMySubscription/{id}', [GarageSubscriptionsController::class,'updateMySubscription']);
        Route::delete('removeSubscription/{id}', [GarageSubscriptionsController::class,'removeSubscription']);
        Route::get('showMyUsersSubscription', [GarageSubscriptionsController::class,'showMyUsersSubscription']);
        Route::get('showAccountUser/{id}', [GaragesController::class,'showAccountUser']);
        Route::get('showReservationsOnMyGarage', [GaragesController::class,'showReservationsOnMyGarage']);
        Route::post('open_close/{id}', [GaragesController::class,'open_close']);
        Route::get('getInvoices', [ReportsController::class,'getInvoices']);
      //  Route::get('getCustomerInvoices', [ReportsController::class,'getCustomerInvoices']);

    });

    Route::group(['middleware'=>'auth.garage_employee'],function (){
        //employee`
        Route::post('employee-logout', [EmployeeAuthController::class, 'employee_logout']);

        Route::post('customerSubscription', [GarageEmployeesController::class,'customerSubscription']);
        Route::post('updateCustomerSubscription', [GarageEmployeesController::class,'updateCustomerSubscription']);
//        Route::get('filterUserSubscriptions/{id}', [UserSubscriptionsController::class,'filterUserSubscriptions']);
        Route::post('deleteUserSubscription/{id}', [SubscriptionsController::class,'deleteUserSubscription']);
        Route::post('wParkCustomer', [WParksCustomerController::class,'wParkCustomer']);
        Route::post('wParkuser', [WParksController::class,'wParkuser']);
   //arwa
        Route::get('getMyAccountInfo', [GarageEmployeesController::class,'getMyAccountInfo']);
        Route::post('updateMyInfo', [GarageEmployeesController::class,'updateMyInfo']);
        Route::get('showAccountUser', [GarageEmployeesController::class,'showAccountUser']);
        Route::get('getUsersReservations', [GarageEmployeesController::class,'getUsersReservations']);
        Route::get('showMyUsersServices', [GarageEmployeesController::class,'showMyUsersServices']);
        Route::post('addCustmer', [CustomersController::class,'addCustmer']);

        Route::post('userEntry', [ActiveUsersController::class,'userEntry']);
        Route::post('exitUser', [ActiveUsersController::class,'exitUser']);

        Route::post('customerEntry', [ActiveUsersController::class,'customerEntry']);
        Route::post('exitCustomer', [ActiveUsersController::class,'exitCustomer']);

        Route::get('getCustomersInGarage', [CustomersController::class,'getCustomersInGarage']);
        Route::get('showCoustemr/{id}', [CustomersController::class,'showCoustemr']);

        Route::post('services_done', [ServicesController::class,'services_done']);
        Route::post('services_customer_done', [ServicesController::class,'services_customer_done']);
        Route::post('makePayment', [PayFeesController::class,'makePayment']);
        Route::get('getActiveUserWithWallet', [ActiveUsersController::class,'getActiveUserWithWallet']);
        Route::get('getActiveCustomer', [ActiveUsersController::class,'getActiveCustomer']);

        Route::get('garage_employee_services', [ServicesController::class,'garage_employee_services']);
        Route::post('requestCustomerServices', [ServicesController::class,'requestCustomerServices']);
    });

    ///////////////////////محاسب
    Route::get('getWalletBalance/{userId}', [UserController::class, 'getWalletBalance']);
    Route::get('searchByNameforUser', [UserController::class, 'searchByNameforUser']);
    Route::post('addTOWalletBalance/{userId}', [UserController::class, 'addTOWalletBalance']);

    Route::get('getUsersIdsAndNames', [UserController::class, 'getUsersIdsAndNames']);
//////////////////سيستم مانجر
    Route::get('getAllGarages', [GaragesController::class, 'getAllGarages']);

});
Route::get('getAllGarages', [GaragesController::class, 'getAllGarages']);
