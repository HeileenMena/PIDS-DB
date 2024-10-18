document.addEventListener('DOMContentLoaded', function () {
    const pageId = document.body.id;  // Obtiene el ID de la página desde el body
    fetchData(pageId);  // Envía el ID de la página como parámetro
});

//Función para cargar la información dependiendo la pagina que se abra
function fetchData(pageId) {
    // Enviar el parámetro de la página a PHP
    fetch(`fetch_data.php?page=${pageId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const tableBody = document.getElementById('dataTable').querySelector('tbody');
            tableBody.innerHTML = '';  // Limpiar el cuerpo de la tabla

            // Llenar la tabla con los datos
            data.forEach(item => {
                const row = document.createElement('tr');
                // Para cada página, los campos a mostrar son diferentes
                let rowHTML = '';
                for (const key in item) {
                    rowHTML += `<td>${item[key]}</td>`;
                }
                row.innerHTML = rowHTML;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
        });
}

// Función para dar de alta registros
document.addEventListener('DOMContentLoaded', function () {
    const pageId = document.body.id;  // Obtiene el id del body de la página
    const addRecordBtn = document.getElementById('addRecordBtn');  // Botón para añadir registros
    const addRecordDialog = document.getElementById('addRecordDialog');  // Modal
    const saveRecordBtn = document.getElementById('saveRecordBtn');  // Botón para guardar el registro
    const cancelBtn = document.getElementById('cancelBtn');  // Botón para cancelar

    // Campos dinámicos por página
    const formFields = {
        'SErbu': ['chasis', 'fan', 'power', 'rsp', 'fc'],
        'SFretta': ['chasis', 'fan', 'power'],
        'SInsbu': ['chasis', 'fan', 'power'],
        'SPabu': ['chasis', 'fan', 'power', 'rsp', 'ima'],
        'TestingPathPabu': ['pid', 'sysassy', 'syshipot', 'sysft', 'test_station']
    };

    // Crear el formulario dinámicamente dependiendo de la página
    function setupForm() {
        const fields = formFields[pageId];
        const formContainer = document.getElementById('formFieldsContainer');
        formContainer.innerHTML = ''; // Limpiar el contenedor

        fields.forEach(field => {
            const label = document.createElement('label');
            label.setAttribute('for', field + 'Input');
            label.textContent = field.toUpperCase() + ':';

            const input = document.createElement('input');
            input.type = 'text';
            input.id = field + 'Input';
            input.placeholder = 'Ingrese ' + field.toUpperCase();

            formContainer.appendChild(label);
            formContainer.appendChild(input);
            formContainer.appendChild(document.createElement('br'));
        });
    }

    // Abre el modal para añadir registros
    addRecordBtn.addEventListener('click', function () {
        setupForm();  // Configura el formulario según la página actual
        addRecordDialog.style.display = 'block';
    });

    // Cerrar el modal cuando se presiona "Cancelar"
    cancelBtn.addEventListener('click', function () {
        addRecordDialog.style.display = 'none';
    });

    // Guardar los datos en la base de datos
    saveRecordBtn.addEventListener('click', function () {
        const data = {};
        const fields = formFields[pageId];

        // Recolectar los valores de los campos del formulario
        let isValid = true; // Flag para verificar si todos los campos están completos
        let incompleteFields = []; // Guardamos los campos incompletos

        fields.forEach(field => {
            const inputElement = document.getElementById(field + 'Input');
            if (!inputElement.value) {
                isValid = false; // Si hay algún campo vacío, es inválido
                incompleteFields.push(field); // Guardamos el campo incompleto
            }
            data[field] = inputElement.value; // Guardamos el valor, aunque esté vacío
        });

        // Si algún campo está vacío, pedir confirmación antes de guardar
        if (!isValid) {
            const incompleteFieldsList = incompleteFields.map(field => field.toUpperCase()).join(', '); 
            const confirmMessage = `Faltan los siguientes campos: ${incompleteFieldsList}. ¿Desea continuar y guardar el registro de todos modos?`;

            if (!window.confirm(confirmMessage)) {
                return; // Si el usuario cancela, no enviamos el registro
            }
        }

        // Enviar los datos al archivo PHP usando fetch
        fetch('add_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `page=${pageId}&${new URLSearchParams(data).toString()}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Registro añadido con éxito.');
                addRecordDialog.style.display = 'none';
                fetchData(pageId);  // Refresca la tabla con los datos actualizados
            } else {
                alert('Error al añadir el registro.');
            }
        })
        .catch(error => {
            console.error('Error al añadir el registro:', error);
        });
    });
});







// JavaScript para ocultar o mostrar la barra de inicio al hacer scroll
var lastScrollTop = 0;
var header = document.getElementById("header");

window.addEventListener("scroll", function() {
    var currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > lastScrollTop) {
        header.style.top = "-100px"; // Ocultar la barra de inicio al hacer scroll hacia abajo
    } else {
        header.style.top = "0"; // Mostrar la barra de inicio al hacer scroll hacia arriba
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // Para dispositivos moviles
});

// Funcion para buscar en la tabla por No. de Parte
function searchPartNumber() {
    var input = document.getElementById('searchInput');
    var filter = input.value.toUpperCase();
    var table = document.getElementById('dataTable');
    var tr = table.getElementsByTagName('tr');

    for (var i = 0; i < tr.length; i++) {
        var tdArray = tr[i].getElementsByTagName('td');
        var found = false;

        for (var j = 0; j < tdArray.length; j++) {
            if (tdArray[j]) {
                var textValue = tdArray[j].textContent || tdArray[j].innerText;
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }

        if (found) {
            tr[i].style.display = "";
        } else {
            if (tr[i].classList.contains('header-row')) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
