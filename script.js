document.addEventListener('DOMContentLoaded', function () {
    const pageId = document.body.id;  // Obtiene el ID de la página desde el body
    fetchData(pageId);  // Envía el ID de la página como parámetro
});

// Función para cargar la información dependiendo de la página que se abra
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
                let rowHTML = '';

                // Para cada página, los campos a mostrar son diferentes
                for (const key in item) {
                    rowHTML += `<td>${item[key]}</td>`;
                }

                // Agregar una nueva celda para el botón de editar
                rowHTML += `<td><button class="btn btn-edit" onclick="editRecord(${item.id})">Editar</button></td>`;

                // Agregar una nueva celda para el botón de eliminar
                rowHTML += `<td><button class="btn btn-delete" onclick="deleteRecord(${item.id}, '${pageId}')">Eliminar</button></td>`;

                row.innerHTML = rowHTML;
                tableBody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error al cargar los datos:', error);
        });
}

// Función para editar registros
document.addEventListener('DOMContentLoaded', function () {
    let currentRecordId = null;

    // Función que se llama cuando se presiona el botón "Editar"
    window.editRecord = function (recordId) {
        const pageId = document.body.id;  // Obtiene el ID de la página actual (SErbu, etc.)
        currentRecordId = recordId;  // Almacena el ID del registro editado

        // Mostrar el modal de edición
        document.getElementById('editRecordDialog').style.display = 'block';

        // Configura el formulario de acuerdo a la página actual
        setupEditForm(pageId);

        // Realizar una solicitud a PHP para obtener los datos del registro
        fetch('fetch_data1.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `page=${pageId}&id=${recordId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.record) {
                const record = data.record;
                const fields = formFields[pageId];
                fields.forEach(field => {
                    const inputElement = document.getElementById(field + 'Input');
                    if (inputElement && record[field] !== undefined) {
                        inputElement.value = record[field];  // Asignar valor
                    }
                });
            } else {
                alert('No se encontraron los datos del registro.');
            }
        })
        .catch(error => {
            console.error('Error al obtener el registro:', error);
        });
    };

    // Configurar los campos del formulario dinámicamente según la página
    function setupEditForm(pageId) {
        const fields = formFields[pageId];  // Obtiene los campos correspondientes
        const formContainer = document.getElementById('editFormFieldsContainer');
        formContainer.innerHTML = '';  // Limpia el formulario

        fields.forEach(field => {
            const label = document.createElement('label');
            label.setAttribute('for', field + 'Input');
            label.textContent = field.toUpperCase() + ':';  // Añade la etiqueta del campo

            const input = document.createElement('input');
            input.type = 'text';
            input.id = field + 'Input';
            input.placeholder = 'Ingrese ' + field.toUpperCase();

            formContainer.appendChild(label);
            formContainer.appendChild(input);
            formContainer.appendChild(document.createElement('br'));  // Añadir salto de línea
        });
    }

    // Definir los campos para cada página
    const formFields = {
        'SErbu': ['chasis', 'fan', 'power', 'rsp', 'fc'],
        'SFretta': ['chasis', 'fan', 'power'],
        'SInsbu': ['chasis', 'fan', 'power'],
        'SPabu': ['chasis', 'fan', 'power', 'rsp', 'ima'],
        'TestingPathPabu': ['pid', 'sysassy', 'syshipot', 'sysft', 'test_station']
    };

    // Función para guardar los cambios
    document.getElementById('saveEditRecordBtn').addEventListener('click', function () {
        const pageId = document.body.id;
        const fields = formFields[pageId];
        const data = {};
        let isValid = true;
        let incompleteFields = [];

        // Recolectar los valores de los campos del formulario
        fields.forEach(field => {
            const inputElement = document.getElementById(field + 'Input');
            if (!inputElement.value) {
                isValid = false;
                incompleteFields.push(field);  // Guardar el campo vacío
            }
            data[field] = inputElement.value;
        });

        // Validar si algún campo está vacío
        if (!isValid) {
            const incompleteFieldsList = incompleteFields.map(field => field.toUpperCase()).join(', ');
            const confirmMessage = `Faltan los siguientes campos: ${incompleteFieldsList}. ¿Desea continuar y actualizar el registro de todos modos?`;

            if (!window.confirm(confirmMessage)) {
                return;  // Si el usuario cancela, no enviamos los datos
            }
        }

        // Enviar los datos al archivo PHP para actualizar el registro utilizando PUT
        fetch('update_record.php', {
            method: 'PUT',  // Cambiamos de POST a PUT
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `page=${pageId}&id=${currentRecordId}&${new URLSearchParams(data).toString()}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Registro actualizado con éxito.');
                document.getElementById('editRecordDialog').style.display = 'none';  // Cerrar el modal
                fetchData(pageId);  // Refrescar los datos de la tabla
            } else {
                alert('Error al actualizar el registro.');
            }
        })
        .catch(error => {
            console.error('Error al actualizar el registro:', error);
        });
    });

    // Cerrar el modal cuando se presiona "Cancelar"
    document.getElementById('cancelEditBtn').addEventListener('click', function () {
        document.getElementById('editRecordDialog').style.display = 'none';
    });
});

// Función para eliminar un registro
function deleteRecord(recordId, tableName) {
    if (confirm(`¿Estás seguro de que quieres eliminar el registro con ID: ${recordId} de la tabla ${tableName}?`)) {
        // Hacer una solicitud para eliminar el registro
        fetch('delete_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${recordId}&table=${tableName}` // Enviamos tanto el ID como el nombre de la tabla
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registro eliminado con éxito');
                    fetchData(tableName); // Recargar los datos de la tabla correspondiente
                } else {
                    alert('Hubo un problema al eliminar el registro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error al eliminar el registro:', error);
            });
    }
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

// Ocultar la barra de inicio al hacer scroll hacia abajo
window.addEventListener("scroll", function () {
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

