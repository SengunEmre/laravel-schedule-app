<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>One Line Schedule Data</title>
    <style>
        .maincontent {
            margin-top: 20px;
        }
        .expandable {
            cursor: pointer;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            margin-bottom: 5px;
        }
        .collapsed {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .expanded {
            max-height: 1000px; /* Arbitrary large value */
            transition: max-height 0.3s ease-in;
            margin-left: 20px;
        }
        .sub-item {
            margin-left: 20px;
            padding: 5px;
            border-left: 2px solid #ccc;
            margin-bottom: 5px;
        }
        .total {
            margin-left: 20px;
            margin-bottom: 5px;
            overflow-y: auto;

        }
    </style>
</head>

<div id="dataCount" class="total"></div>
<div id="mainContent">
    <!-- Collapsible Divs for each schedule -->
</div>

<script>
    var rawData = '{!! addslashes(json_encode($data)) !!}';
    let SheduleData = JSON.parse(rawData);

    function formatDate(dateString) {
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return new Date(dateString).toLocaleDateString('en-UK', options);
    }

    function toggleDiv(id) {
        const element = document.getElementById(id);
        if (element.classList.contains('collapsed')) {
            element.classList.remove('collapsed');
            element.classList.add('expanded');
        } else {
            element.classList.remove('expanded');
            element.classList.add('collapsed');
        }
    }

    let totalSchedules = 0;

    for (const key in SheduleData) {
        if (SheduleData.hasOwnProperty(key)) {
            const data = SheduleData[key];
            totalSchedules += data.length;

            const expandableDiv = document.createElement('div');
            expandableDiv.className = "expandable";
            expandableDiv.setAttribute("onclick", `toggleDiv('mainContent${key}')`);

            const infospan = document.createElement('span');
            infospan.innerText = `${data[0].Carrier} ${data.length} Schedules Found`;
            expandableDiv.appendChild(infospan);

            const mainContentDiv = document.createElement('div');
            mainContentDiv.className = "collapsed";
            mainContentDiv.id = `mainContent${key}`;

            data.forEach((item, index) => {
                const scheduleDiv = document.createElement('div');
                scheduleDiv.className = 'expandable';

                const departureDate = new Date(item.PolDeparture);
                const dischargeDate = new Date(item.PodArrival);
                const differenceInMilliseconds = dischargeDate - departureDate;
                const differenceInDays = Math.round(differenceInMilliseconds / (1000 * 60 * 60 * 24));

                scheduleDiv.innerHTML = `Transit Time: ${differenceInDays} days`;
                scheduleDiv.setAttribute('onclick', `toggleDiv('subContent${key}_${index}')`);

                const subContentDiv = document.createElement('div');
                subContentDiv.id = `subContent${key}_${index}`;
                subContentDiv.className = 'collapsed sub-item';

                let tableHTML = `
                    <table border="1" cellpadding="5" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Arrival Date</th>
                                <th>Port of Discharge/Load</th>
                                <th>Departure Date</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                // Add the first row for the Port of Loading
                tableHTML += `
                    <tr>
                        <td></td>
                        <td>${item.PolName} (${item.PolCode})</td>
                        <td>${formatDate(item.PolDeparture)}</td>
                    </tr>
                `;

                // Iterate through the ts object to add the transshipment ports
                for (const key in item.ts) {
                    if (item.ts.hasOwnProperty(key)) {
                        const ts = item.ts[key];
                        tableHTML += `
                            <tr>
                                <td>${formatDate(ts.Arrival)}</td>
                                <td>${ts.Port} (${ts.PortCode})</td>
                                <td>${formatDate(ts.Departure)}</td>
                            </tr>
                        `;
                    }
                }

                // Add the last row for the Port of Discharge
                tableHTML += `
                    <tr>
                        <td>${formatDate(item.PodArrival)}</td>
                        <td>${item.PodName} (${item.PodCode})</td>
                        <td></td>
                    </tr>
                `;

                tableHTML += `
                        </tbody>
                    </table>
                `;

                subContentDiv.innerHTML = tableHTML;

                mainContentDiv.appendChild(scheduleDiv);
                mainContentDiv.appendChild(subContentDiv);
            });

            document.getElementById('mainContent').appendChild(expandableDiv);
            document.getElementById('mainContent').appendChild(mainContentDiv);
        }
    }

    document.getElementById('dataCount').innerText = `${totalSchedules} Schedules Found`;
    
</script>
