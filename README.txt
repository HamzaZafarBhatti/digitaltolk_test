Code Pros:

1) The code is written with Repository Pattern.
2) DRY is used.

Code Refactor:
1) Updated PHP coding style is used.
2) Return early method is used.
3) Instead of env(), config() should be used.
4) Laravel auth() is used.
5) As the namespace in BookingController shows that the controller is used for Api, we can use Laravel API resources or JsonResponse to send response back.
6) Laravel Request Validation is used. 
7) Route-Model binding.
8) Used traits to return response in json format. We can also use helper class. 
9) Error Logging.
10) Try/Catch added.