<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Search</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <style>
        .row {
            margin-bottom: 50px; 
        }
        .autocomplete-items {
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            position: absolute;
            background-color: white;
            max-height: 150px;
            overflow-y: auto;
            width: 200px;
        }
        .autocomplete-items div {
            padding: 2px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .btn-primary:hover {
            background-color: #0056b3; 
            border-color: #004085;     
            
        }

    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="d-inline-flex align-items-center position-relative">
                    <div class="position-relative">
                        <input type="text" id="from-search" class="form-control" placeholder="Search From...">
                        <div id="from-autocomplete-list" class="autocomplete-items"></div>
                    </div>
                    <span class="mx-2">TO</span>
                    <div class="position-relative">
                        <input type="text" id="to-search" class="form-control" placeholder="Search To...">
                        <div id="to-autocomplete-list" class="autocomplete-items"></div>
                    </div>
                    <span class="mx-2"></span>
                    <button type="button" id="search-button" class="btn btn-primary btn-lm rounded-pill px-4 shadow">
                        Search
                    </button>
                </div>
            </div>
        </div>    
    <div id="scheduleResults"></div>
    </div>
    <!-- Include the oneline-results view -->

        
    <script >
        $(document).ready(function() {
            function fetchPorts(query, listId) {
                $.ajax({
                    url: '/ports', 
                    type: 'GET',
                    data: { q: query },
                    success: function(data) {
                        var list = $(listId);
                        list.empty();

                        $.each(data, function(key, port) {
                            var item = $('<div>').text(port.name).on('click', function() {
                                $(listId).prev('input').val(port.name);
                                list.empty(); 
                            });
                            list.append(item);
                        });
                    }
                });
            }

            $('#from-search').on('input', function() {
                var query = $(this).val();
                if (query.length > 0) {
                    fetchPorts(query, '#from-autocomplete-list');
                } else {
                    $('#from-autocomplete-list').empty();
                }
            });

            $('#to-search').on('input', function() {
                var query = $(this).val();
                if (query.length > 0) {
                    fetchPorts(query, '#to-autocomplete-list');
                } else {
                    $('#to-autocomplete-list').empty();
                }
            });

            // Close the dropdown if clicking outside
            $(document).click(function(e) {
                if (!$(e.target).closest('#from-search, #from-autocomplete-list').length) {
                    $('#from-autocomplete-list').empty();
                }
                if (!$(e.target).closest('#to-search, #to-autocomplete-list').length) {
                    $('#to-autocomplete-list').empty();
                }
            });
                // Handle the search button click

           $('#search-button').on('click', function() {
                SheduleData = null;
                var fromPort = $('#from-search').val();
                var toPort = $('#to-search').val();
                if (fromPort && toPort) {
                    if (fromPort == toPort) {
                        alert('Both fields cannot be the same.');
                        return;
                    }

                    $.ajax({
                        url: '{{ route("get-port-code") }}',
                        type: 'GET',
                        data: {
                            fromPort: fromPort,
                            toPort: toPort
                        },
                        success: function(data) {
                            console.log("From Port Code:", data.fromPortCode);
                            console.log("To Port Code:", data.toPortCode);
                            
                            $.ajax({
                                url: '/fetch-schedule',
                                type: 'GET',
                                data: {
                                    pol : data.fromPortCode,
                                    pod : data.toPortCode
                                },
                                success: function(response) {
                                    $('#scheduleResults').html(response);
                                },
                                error: function() {
                                    alert('Failed to scrape the website.');
                                }
                            });
                        },
                        error: function() {
                            alert('Failed to fetch port codes. Please try again.');
                        }
                    });
                } else {
                    alert('Please fill in both the "From" and "To" fields.');
                }
            });
            
        });
        
    </script>


    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
