import axios from 'axios';

const baseUrl = 'https://webtracking.arkasline.com.tr/api/request/Get?controllerMethod=webtracking%2Fapi%2Fport%2FGetAllByKeyword&prms=';

const headers = {
  "accept": "application/json, text/plain, */*",
  "accept-language": "tr-TR,tr;q=0.9,en-US;q=0.8,en;q=0.7,ja;q=0.6",
  "cache-control": "no-cache",
  "correlationid": "7d7efd5f-f890-423b-9ad7-fa2d74e4e980",
  "culture": "en-US",
  "if-modified-since": "Mon, 26 Jul 1997 05:00:00 GMT",
  "pragma": "no-cache",
  "priority": "u=1, i",
  "sec-ch-ua": "\"Not/A)Brand\";v=\"8\", \"Chromium\";v=\"126\", \"Google Chrome\";v=\"126\"",
  "sec-ch-ua-mobile": "?0",
  "sec-ch-ua-platform": "\"Windows\"",
  "sec-fetch-dest": "empty",
  "sec-fetch-mode": "cors",
  "sec-fetch-site": "same-origin",
  "cookie": "__utmz=230835535.1709288582.3.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); __utma=230835535.2106566428.1690361755.1709288582.1709293603.4; ApplicationGatewayAffinityCORS=a8947a733916473e4f9631c7c757cbe4; ApplicationGatewayAffinity=a8947a733916473e4f9631c7c757cbe4; ARRAffinity=52eed1d4bad3af19da0bcae7a2cf98309da60d1db6bc33aef89ca521897f539b; ARRAffinitySameSite=52eed1d4bad3af19da0bcae7a2cf98309da60d1db6bc33aef89ca521897f539b",
  "Referer": "https://webtracking.arkasline.com.tr/routefinder",
  "Referrer-Policy": "strict-origin-when-cross-origin"
};

async function fetchData(url) {
  try {
    const response = await axios.get(url, { headers });
    return response.data;
  } catch (error) {
    console.error('Error fetching data:', error);
    return null;
  }
}

function createUrl(portcode) {
  const params = encodeURIComponent(JSON.stringify({ keyword: portcode }));
  return `${baseUrl}${params}`;
}

async function getPortId(portcode) {
  const url = createUrl(portcode);
  try {
    const data = await fetchData(url);
    return data ? data.data[0].id : null;
  } catch (error) {
    console.error("ARKAS DO NOT HAVE THE PORT " + portcode);
    return null;
  }
}

export default async function arkasRequest(pol, pod ) {
    const date = "2024-08-12" 
  try {
    let fromPortId = await getPortId(pol);
    let toPortId = await getPortId(pod);

    if (!fromPortId || !toPortId) {
      console.error('PORTS ARE NOT VALID FOR ARKAS LINE');
      return null;
    }

    const departureDate = date;
    const noOfWeeks = "3";
    const requestUrl = `https://webtracking.arkasline.com.tr/api/request/Get?controllerMethod=webtracking%2Fapi%2Froutefinder%2FGetVoyages&prms=${encodeURIComponent(JSON.stringify({
      fromPortId,
      toPortId,
      departureDate,
      noOfWeeks,
      pageIndex: 0,
      pageSize: 0
    }))}`;

    const response = await axios.get(requestUrl, { headers });
    const data = response.data;
    if (!data || !data.data || !data.data.data || !data.data.data.length) {
      console.error('Error: Invalid response data');
      return null;
    }
    const sumdata = data.data.data
    const scheduleData = {}
    for (let i = 0; i < sumdata.length; i++) {
        scheduleData[`${i}`] = await fetchRouteDetails(sumdata[i].voyageRouteId);;
    }
    return scheduleData;
} catch (error) {
    console.error('Error:', error);
    return null;
  }
}

async function fetchRouteDetails(voyageRouteId) {
  const params = encodeURIComponent(JSON.stringify({ voyageRouteId }));
  const requestUrl = `https://webtracking.arkasline.com.tr/api/request/Get?controllerMethod=webtracking%2Fapi%2Froutefinder%2FGetRouteDetails&prms=${params}`;

  try {
    const response = await axios.get(requestUrl, { headers });
    return response.data ? response.data.data[0] : null;
  } catch (error) {
    console.error('Error fetching route details:', error);
    return null;
  }
}



export function transformArkasData(data) {
     try {
       if (!data) {
         console.error('Error while formatting');
         return null;
       }
       
       const scheduleinfo = data;
   
       const legs = scheduleinfo.voyageRouteLegCalls.length;
       const scheduleData = {};
       scheduleData.ts = {};
       scheduleData.Carrier = "ARKAS";

       if (legs === 2) {
         scheduleData.polName = scheduleinfo.voyageRouteLegCalls[0]["portName"];
         scheduleData.PolCode = null ;
         scheduleData.PolDeparture = scheduleinfo.voyageRouteLegCalls[0]["departureDate"];
         scheduleData.PodName = scheduleinfo.voyageRouteLegCalls[1]["portName"];
         scheduleData.PodCode = null ;
         scheduleData.PodArrival = scheduleinfo.voyageRouteLegCalls[1]["arrivalDate"];
       } else if (legs === 3){
           const tsKey = `ts1`;
           scheduleData.ts[tsKey] = {
           Arrival:  scheduleinfo.voyageRouteLegCalls[1]["arrivalDate"],
           Port: scheduleinfo.voyageRouteLegCalls[1]["portName"],
           PortCode: null ,
           Departure:  scheduleinfo.voyageRouteLegCalls[1]["departureDate"]
           }
           scheduleData.polName = scheduleinfo.voyageRouteLegCalls[0]["portName"];
           scheduleData.PolCode = null ;
           scheduleData.PolDeparture =  scheduleinfo.voyageRouteLegCalls[0]["departureDate"];
           scheduleData.PodName = scheduleinfo.voyageRouteLegCalls[2]["portName"];
           scheduleData.PodCode = null ;
           scheduleData.PodArrival =  scheduleinfo.voyageRouteLegCalls[2]["arrivalDate"];
        } else {
        for (let i = 0; i < legs; i++) 
        {
           if (i === 0) {
           scheduleData.PolName = scheduleinfo.voyageRouteLegCalls[i]["portName"];
           scheduleData.PolCode = null;
           scheduleData.PolDeparture =  scheduleinfo.voyageRouteLegCalls[i]["departureDate"];
           
         } else if (i === legs - 1) {
           scheduleData.PodName = scheduleinfo.voyageRouteLegCalls[i]["portName"];
           scheduleData.PodCode = null;
           scheduleData.PodArrival =  scheduleinfo.voyageRouteLegCalls[i]["departureDate"];
         } else {
           const tsKey = `ts${i}`;
           scheduleData.ts[tsKey] = {
           Arrival:  scheduleinfo.voyageRouteLegCalls[i]["arrivalDate"],  
           Port: scheduleinfo.voyageRouteLegCalls[i]["portName"],
           PortCode: null,
           Departure:  scheduleinfo.voyageRouteLegCalls[i]["departureDate"]
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
  


const args = process.argv.slice(2); 

// Check if the correct number of arguments were passed
if (args.length < 2) {
    console.error('Please provide both pol and pod arguments.');
    process.exit(1);
}

// Assign the arguments to pol and pod
const pol = args[0];
const pod = args[1];
  // Call the function with the provided arguments
arkasRequest(pol, pod).then(data => {
    if (data) {
        let ArkasLinescheduleData = {};
        //console.log(data[0]);
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const scheduleLine = data[key];
                ArkasLinescheduleData[key] = transformArkasData(scheduleLine);
            }
        }
        console.log(JSON.stringify(ArkasLinescheduleData, null, 2));
    } else {
        console.error('Failed to fetch schedule data.');öm 
    }
});