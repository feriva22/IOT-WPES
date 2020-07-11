<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data Sensor</title>

    <!-- SCRIPT CSS PLUGIN-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>

</head>
<body>
    <main role="main">
        <section class="jumbotron text-center">
            <div class="container">
              <h1 class="jumbotron-heading">Data Sensor Deteksi Ahay</h1>
              <p>
                <button id="refresh-data" class="btn btn-primary my-2">Refresh Data</a>
              </p>
            </div>
        </section>
        <div class="container">
            <div class="row" id="all-device">
                            
            </div>
        </div>
    </main>
    <!-- SCRIPT JS LOAD-->
    <script src="assets/jquery-3.4.1.min.js"></script>
    <script>
        var device_data = null;

        $(document).ready(function(){
            get_alldevice();

            $('#refresh-data').on('click',function(e){
                $('#all-device').empty();
                get_alldevice();
            })

            setInterval(function(){ 
                update_sensor();
            }, 3000);


        });

        function update_sensor(){
            $(document).ready(function(){
                $.ajax({
                   url: 'http://localhost:8080/get.php',
                   method: 'get',
                   dataType: 'json',
                   success: function(resp){
                        data = resp.data;
                        if(data.length > 0 && data.length == device_data.length){
                            $.each(data,(idx,val) => {
                                $(`.sensor1[data-id="${val.id}"]`).html(`Sensor 1 : ${val.sensor1}`);
                                $(`.sensor2[data-id="${val.id}"]`).html(`Sensor 2 : ${val.sensor2}`);
                            })
                        }else{
                            get_alldevice();
                        }
                   },
                   error: function(jxHr){

                   }
               }) 
            })
        }

        function get_alldevice(){
            $(document).ready(function(){
               $.ajax({
                   url: 'http://localhost:8080/get.php',
                   method: 'get',
                   dataType: 'json',
                   success: function(resp){
                        
                        data = resp.data;
                        device_data = data;
                        if(data.length > 0){
                            $.each(data,(idx,val) => {
                                $('#all-device').append(`
                                <div class="col-md-6">
                                    <div class="card mb-4 box-shadow">
                                      <div class="card-body">
                                        <p class="card-text">ID : ${val.id}</p>
                                        <div class="sensor1" data-id="${val.id}">Sensor 1 : ${val.sensor1}</div>
                                        <div class="sensor2" data-id="${val.id}">Sensor 2 : ${val.sensor2}</div>
                                        <div class="d-flex justify-content-between align-items-center">
                                          <small class="text-muted">Last update : ${val.last_update}</small>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                `);
                            })
                        }else{
                            $('#all-device').append(`
                                <p>No device Found</p>
                            `);
                        }
                   },
                   error: function(jxHr){

                   }
               }) 
            })
        }

        
    
    </script>
</body>
</html>