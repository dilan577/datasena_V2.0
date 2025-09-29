describe('Formulario Registro de Empresa', () => {
  beforeEach(() => {
    cy.visit('http://localhost/datasena_v2.0/super-administrador/empresa/empresaRe_su.php');
  });

  it('Debería registrar una empresa correctamente', () => {
    // Seleccionar tipo de documento
    cy.get('#tipo_documento').select('NIT');

    // Llenar campos de texto
    cy.get('#numero_identidad').type('12345678');
    cy.get('#nickname').type('EmpresaPrueba');
    cy.get('#telefono').type('3001234567');
    cy.get('#correo').type('empresa@correo.com');
    cy.get('#direccion').type('Calle Falsa 123');
    cy.get('#actividad_economica').type('Servicios');

    // Seleccionar estado
    cy.get('#estado').select('1'); // 1 = Activo

    // Contraseña
    cy.get('#contrasena').type('Prueba123!');
    cy.get('#confirmar_contrasena').type('Prueba123!');

    // Enviar formulario
    cy.get('button[type="submit"]').contains('Registrar Empresa').click();

    // Validar mensaje de éxito
    cy.contains('Empresa registrada exitosamente').should('be.visible');
  });

  it('Debería mostrar errores si los campos obligatorios están vacíos', () => {
    // Solo dar submit sin llenar campos
    cy.get('button[type="submit"]').contains('Registrar Empresa').click();

    // Validar algunos mensajes de error visibles
    cy.contains('Este campo es obligatorio.').should('be.visible');
    cy.contains('La contraseña es obligatoria.').should('be.visible');
  });

  it('Debería mostrar error si las contraseñas no coinciden', () => {
    // Llenar algunos campos requeridos
    cy.get('#tipo_documento').select('NIT');
    cy.get('#numero_identidad').type('12345678');
    cy.get('#nickname').type('EmpresaPrueba');
    cy.get('#telefono').type('3001234567');
    cy.get('#correo').type('empresa@correo.com');
    cy.get('#direccion').type('Calle Falsa 123');
    cy.get('#actividad_economica').type('Servicios');
    cy.get('#estado').select('1');

    // Contraseñas diferentes
    cy.get('#contrasena').type('Prueba123!');
    cy.get('#confirmar_contrasena').type('Prueba321!');

    cy.get('button[type="submit"]').contains('Registrar Empresa').click();

    cy.contains('Las contraseñas no coinciden').should('be.visible');
  });
});
