// Render / Init block
document.addEventListener("DOMContentLoaded", function () {
  const datesDiv = document.getElementById("chartLabels");
  const dates = JSON.parse(datesDiv.dataset.chartdates);

  const valuesDiv1 = document.getElementById("chartData1");
  const valuesPreventive = JSON.parse(valuesDiv1.dataset.dataset1);

  const valuesDiv2 = document.getElementById("chartData2");
  const valuesCurative = JSON.parse(valuesDiv2.dataset.dataset2);

  const valuesDiv = document.getElementById("chartData3");
  const partsValue = JSON.parse(valuesDiv.dataset.dataset3);

  //----------------------------------------------------------MORE INFOS----------------------------

  let numberOfPreventiveBt = 0;
  for (let i = 0; i < valuesPreventive.length; i++) {
    numberOfPreventiveBt += valuesPreventive[i];
  }

  let numberOfBtCurative = 0;
  for (let i = 0; i < valuesCurative.length; i++) {
    numberOfBtCurative += valuesCurative[i];
  }

  let numberOfTotalBt = numberOfBtCurative + numberOfPreventiveBt;

  // TOTAL OF BT---------------------------------------------------
  const totalOfBt = document.getElementById("totalBtNumber");
  let totalBt = document.createElement("span");
  totalBt.innerText = numberOfTotalBt;
  totalOfBt.appendChild(totalBt);

  // TOTAL OF PREVENTIVE BT----------------------------------------
  const totalOfpreventiveBt = document.getElementById("totalBtPréventifNumber");
  let totalPrevBt = document.createElement("span");
  totalPrevBt.innerText = numberOfPreventiveBt;
  totalOfpreventiveBt.appendChild(totalPrevBt);

  // TOTAL OF CURATIVE BT-----------------------------------------
  const totalOfCurativeBt = document.getElementById("totalBtCuratifNumber");
  let totalCurBt = document.createElement("span");
  totalCurBt.innerText = numberOfBtCurative;
  totalOfCurativeBt.appendChild(totalCurBt);

  // ----------------------------------------------------------GRAPH 1 PREVENTIF VS CURATIF----------------------------
  // Setup block
  let data = {
    labels: dates,
    datasets: [
      {
        label: "Préventif",
        data: partsValue,
        backgroundColor: ["lightgreen"],
        borderColor: ["black"],
        borderWidth: 1,
      },
      {
        label: "Curatif",
        data: valuesCurative,
        backgroundColor: ["orange"],
        borderColor: ["black"],
        borderWidth: 1,
      },
    ],
  };

  // Config block
  let config = {
    type: "bar",
    data,
    options: {
      responsive: true,
      elements: {},
      scales: {
        x: {
          title: {
            color: "black",
            display: true,
            text: "Dates (mois)",
          },
          grid: {
            color: "blue",
          },
          ticks: {
            color: "red",
            display: true,
          },
        },
        y: {
          title: {
            color: "black",
            display: true,
            text: "Temps (min)",
          },
          grid: {
            color: "blue",
          },
          ticks: {
            // Include a dollar sign in the ticks
            callback: function (value, index, ticks) {
              return value + " min";
            },
          },
          beginAtZero: false,
        },
      },
      plugins: {
        subtitle: {
          display: true,
          text: "Préventif VS Curatif",
          color: "blue",
          font: {
            size: 20,
            family: "tahoma",
          },
          padding: {
            bottom: 20,
          },
        },
      },
    },
  };

  try {
    const ctx1 = document.getElementById("preventiveCurative").getContext("2d");
    const myChart1 = new Chart(ctx1, config);
  } catch (error) {
    const dateNotOk = document.createElement("p");
    document.getElementById("graphique").prepend(dateNotOk);
    dateNotOk.innerText = "Il n'y a pas de données pour ces dates";
    dateNotOk.className = "no-data";
  }

  // ----------------------------------------------------------GRAPH 2 PIECES DETACHES----------------------------
  // Setup block
  data = {
    labels: dates,
    datasets: [
      {
        label: "Prix pièces",
        data: partsValue,
        backgroundColor: ["darkgreen"],
        borderColor: ["darkgreen"],
        borderWidth: 1,
      },
    ],
  };

  // Config block
  config = {
    type: "line",
    data,
    options: {
      responsive: true,
      elements: {},
      scales: {
        x: {
          title: {
            color: "black",
            display: true,
            text: "Dates (mois)",
          },
          grid: {
            color: "blue",
          },
          ticks: {
            color: "red",
            display: true,
          },
        },
        y: {
          title: {
            color: "black",
            display: true,
            text: "Montant",
          },
          grid: {
            color: "blue",
          },
          ticks: {
            // Include a dollar sign in the ticks
            callback: function (value, index, ticks) {
              return value + " €";
            },
          },
          beginAtZero: false,
        },
      },
      plugins: {
        subtitle: {
          display: true,
          text: "Montant Pièces détachées",
          color: "blue",
          font: {
            size: 20,
            family: "tahoma",
          },
          padding: {
            bottom: 20,
          },
        },
      },
    },
  };

  try {
    const ctx2 = document.getElementById("partsvalue");
    const myChart2 = new Chart(ctx2, config);
  } catch (error) {
    const dateNotOk = document.createElement("p");
    document.getElementById("graphique").prepend(dateNotOk);
    dateNotOk.innerText = "Il n'y a pas de données pour ces dates";
    dateNotOk.className = "no-data";
  }
});
