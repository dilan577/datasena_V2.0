describe('Formulario Registro de Admin', () => {
  beforeEach(() => {
    cy.visit('http://localhost/datasena_v2.0/super-administrador/administrador/crear_administrador_SU.php');
  });

  it('Debería registrar un administrador correctamente', () => {
    // Seleccionar tipo de documento
    cy.get('#tipo_documento').select('CC');

    // Llenar campos
    cy.get('#numero_documento').type('12345678');
    cy.get('#nombres').type('Juan');
    cy.get('#apellidos').type('Pérez');
    cy.get('#nickname').type('adminPrueba');
    cy.get('#correo_electronico').type('admin@correo.com');
    cy.get('#contrasena').type('Admin123!');
    cy.get('#confirmar_contrasena').type('Admin123!');

    // Enviar formulario
    cy.get('button[type="submit"]').contains('Crear').click();

    // Validar mensaje de éxito (alerta)
    cy.on('window:alert', (txt) => {
      expect(txt).to.contains('✅ Administrador creado con éxito');
    });
  });

  it('Debería mostrar error si las contraseñas no coinciden', () => {
    cy.get('#tipo_documento').select('CC');
    cy.get('#numero_documento').type('12345678');
    cy.get('#nombres').type('Juan');
    cy.get('#apellidos').type('Pérez');
    cy.get('#nickname').type('adminPrueba');
    cy.get('#correo_electronico').type('admin@correo.com');
    cy.get('#contrasena').type('Admin123!');
    cy.get('#confirmar_contrasena').type('Admin321!');

    cy.get('button[type="submit"]').contains('Crear').click();

    // Validar que se muestre el mensaje de error
    cy.contains('Las contraseñas no coinciden').should('be.visible');
  });

  it('Debería mostrar errores si los campos obligatorios están vacíos', () => {
    cy.get('button[type="submit"]').contains('Crear').click();

    cy.contains('Número de documento entre 5 y 20 dígitos.').should('be.visible');
    cy.contains('Solo letras y espacios.').should('be.visible');
    cy.contains('Correo electrónico inválido.').should('be.visible');
  });
});
