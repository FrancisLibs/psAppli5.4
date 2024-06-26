// Render / Init block
document.addEventListener("DOMContentLoaded", function () {
  const datesDiv = document.getElementById("chartLabels");
  const valuesDiv1 = document.getElementById("chartData1");
  const valuesDiv2 = document.getElementById("chartData2");
  const valuesDiv = document.getElementById("chartData3");

  const dates = JSON.parse(datesDiv.dataset.chartdates);
  const valuesPreventive = JSON.parse(valuesDiv1.dataset.dataset1);
  const valuesCurative = JSON.parse(valuesDiv2.dataset.dataset2);
  const partsValue = JSON.parse(valuesDiv.dataset.dataset3);

  //----------------------------------------------------------MORE INFOS----------------------------

  let numberOfPreventiveBt = 0;
  for (let i = 0; i < valuesPreventive.length; i++) {
    numberOfPreventiveBt += valuesPreventive[i];
  }

  let numberOfCurativeBt = 0;
  for (let i = 0; i < valuesCurative.length; i++) {
    numberOfCurativeBt += valuesCurative[i];
  }

  let numberOfTotalBt = numberOfCurativeBt + numberOfPreventiveBt;

  // TOTAL OF BT TIME---------------------------------------------------
  const totalOfBt = document.getElementById("totalBtTime");
  totalOfBt.textContent = transformTime(numberOfTotalBt);

  // TOTAL OF PREVENTIVE BT  TIME----------------------------------------
  const totalOfpreventiveBt = document.getElementById("totalBtPréventifTime");
  totalOfpreventiveBt.innerText = transformTime(numberOfPreventiveBt);

  // TOTAL OF CURATIVE BT TIME-----------------------------------------
  const totalOfCurativeBt = document.getElementById("totalBtCuratifTime");
  totalOfCurativeBt.innerText = transformTime(numberOfCurativeBt);

  function transformTime(minutes) {
    if (typeof minutes === "number" && minutes > 0) {
      var nbHour = parseInt(minutes / 60, 10);
      var nbminuteRestante = minutes % 60;
      if (nbHour > 0) {
        return nbHour + "h " + nbminuteRestante + "min";
      } else {
        return nbminuteRestante + "min";
      }
    }
  }

  // ----------------------------------------------------------GRAPH 1 PREVENTIF VS CURATIF----------------------------
  // Setup block
  let data = {
    labels: dates,
    datasets: [
      {
        label: "Préventif",
        data: valuesPreventive,
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
    const ctx = document.getElementById("preventiveCurative").getContext("2d");
    new Chart(ctx, config);
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
    const ctx = document.getElementById("partsvalue");
    new Chart(ctx, config);
  } catch (error) {
    const dateNotOk = document.createElement("p");
    document.getElementById("graphique").prepend(dateNotOk);
    dateNotOk.innerText = "Il n'y a pas de données pour ces dates";
    dateNotOk.className = "no-data";
  }
});
