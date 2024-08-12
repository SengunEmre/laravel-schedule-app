import axios from 'axios';

async function OneRequest(pol, pod) {
    const baseUrl = 'https://ecomm.one-line.com/api/v1/schedule/point-to-point';
    const porCode = pol; // POL
    const delCode = pod; // POD
    const fromDate = '2024-08-12'; // DATES !!!!! !!!!!
    const toDate = '2024-08-26';


    const apiUrl = `${baseUrl}?porCode=${porCode}&delCode=${delCode}&fromDate=${fromDate}&toDate=${toDate}&rcvTermCode=Y&deTermCode=Y&tsFlag=&polCode=&podCode=&searchType=List`;

    try {
        const response = await axios.get(apiUrl);
        return response.data; // Return JSON data directly

    } catch (error) {
        console.error('Error fetching data:', error.message);
        return null; // Return null or an error message if needed
    }
}

// Get the command-line arguments
const args = process.argv.slice(2); // Slice to skip the first two elements (node and script path)

// Check if the correct number of arguments were passed
if (args.length < 2) {
    console.error('Please provide both pol and pod arguments.');
    process.exit(1);
}

// Assign the arguments to pol and pod
const pol = args[0];
const pod = args[1];

// Call the function with the provided arguments
    OneRequest(pol, pod).then(data => {
        if (data) {
            const OneLinescheduleData = {};

            data.scheduleLines.forEach((scheduleLine, index) => {
                OneLinescheduleData[`${index}`] = transformOneData(scheduleLine);
            });
            console.log(JSON.stringify(OneLinescheduleData, null, 2));
        } else {
            console.error('Failed to fetch schedule data.');
        }
    });
// Export the function if needed elsewhere
export default OneRequest;


export function transformOneData(data) {
    try {
  
    const scheduleinfo = data.journeys;
    const legs = scheduleinfo.length;
    const scheduleData = {};
    scheduleData.ts = {};
    scheduleData.Carrier = "ONE LINE";
    
    if (legs === 1) {
      scheduleData.PolName = scheduleinfo[0]["polName"];
      scheduleData.PolCode = scheduleinfo[0]["polCode"];
      scheduleData.PolDeparture = scheduleinfo[0]["departureDate"];
      scheduleData.PodName = scheduleinfo[0]["podName"];
      scheduleData.PodCode = scheduleinfo[0]["podCode"];
      scheduleData.PodArrival = scheduleinfo[0]["berthingDate"];
    } else if (legs === 2){
        const tsKey = `ts1`;
        scheduleData.ts[tsKey] = {
        Arrival: scheduleinfo[0]["berthingDate"],
        Port: scheduleinfo[1]["polName"],
        PortCode: scheduleinfo[1]["polCode"],
        Departure: scheduleinfo[1]["departureDate"]
        }
      scheduleData.PolName = scheduleinfo[0]["polName"];
      scheduleData.PolCode = scheduleinfo[0]["polCode"];
      scheduleData.PolDeparture = scheduleinfo[0]["departureDate"];
      scheduleData.PodName = scheduleinfo[1]["podName"];
      scheduleData.PodCode = scheduleinfo[1]["podCode"];
      scheduleData.PodArrival = scheduleinfo[1]["berthingDate"];
     } else {
  
     for (let i = 0; i < legs; i++) 
     {
        if (i === 0) {
        scheduleData.PolName = scheduleinfo[i]["polName"];
        scheduleData.PolCode = scheduleinfo[i]["polCode"];
        scheduleData.PolDeparture = scheduleinfo[i]["departureDate"];
        
      } else if (i === legs - 1) {
        scheduleData.PodName = scheduleinfo[i]["podName"];
        scheduleData.PodCode = scheduleinfo[i]["podCode"];
        scheduleData.PodArrival = scheduleinfo[i]["berthingDate"];
      } else {
        let k = i-1;
        const tsKey = `ts${i}`;
        scheduleData.ts[tsKey] = {
        Arrival: scheduleinfo[k]["berthingDate"],
        Port: scheduleinfo[i]["polName"],
        PortCode: scheduleinfo[i]["polCode"],
        Departure: scheduleinfo[i]["departureDate"]
        };
      } 
     }
    }
    return scheduleData;
  }  catch (error) {
    console.error('Error:', error);
    return null;
  }
  }
  
