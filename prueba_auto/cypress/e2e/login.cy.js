/// <reference types="cypress" />

const BASE = 'http://localhost';
const LOGIN_PAGE = '/datasena_v2.0/inicio_sesion.html';

// CONTRASEÑA EN CLARO que acabas de fijar en la BD
const ADMIN_PLAIN_PASSWORD = 'Administrador321';
const ADMIN_NICK = 'admin01';

const SUPER_USER = { rol: 'super', usuario: 'superadmin', password: '123' };
const EMPRESA_USER = { rol: 'empresa', usuario: 'empresa01', password: 'Empresa01**' };

describe('Login - DATASENA (Cypress)', () => {

  beforeEach(() => {
    cy.visit(BASE + LOGIN_PAGE);
  });

  it('Login válido - Superadmin -> redirige a super_menu', () => {
    cy.get('#rol').select(SUPER_USER.rol);
    cy.get('input[name="usuario"]').clear().type(SUPER_USER.usuario);
    cy.get('input[name="password"]').clear().type(SUPER_USER.password);
    cy.get('button[type="submit"]').click();
    cy.location('pathname', { timeout: 8000 }).should('include', '/super-administrador/super_menu.html');
  });

  it('Login válido - Admin -> redirige a admin_menu', () => {
    cy.get('#rol').select('admin');
    cy.get('input[name="usuario"]').clear().type(ADMIN_NICK);
    cy.get('input[name="password"]').clear().type(ADMIN_PLAIN_PASSWORD);
    cy.get('button[type="submit"]').click();
    cy.location('pathname', { timeout: 8000 }).should('include', '/administrador/admin_menu.html');
  });

  it('Login válido - Empresa -> redirige a empresa_menu', () => {
    cy.get('#rol').select(EMPRESA_USER.rol);
    cy.get('input[name="usuario"]').clear().type(EMPRESA_USER.usuario);
    cy.get('input[name="password"]').clear().type(EMPRESA_USER.password);
    cy.get('button[type="submit"]').click();
    cy.location('pathname', { timeout: 8000 }).should('include', '/empresa/empresa_menu.html');
  });

  it('Login inválido - alerta de credenciales erróneas', () => {
    cy.on('window:alert', (txt) => {
      expect(txt).to.match(/Usuario o contraseña incorrectos/);
    });

    cy.get('#rol').select('super');
    cy.get('input[name="usuario"]').clear().type('superadmin');
    cy.get('input[name="password"]').clear().type('wrong-password-123');
    cy.get('button[type="submit"]').click();
    cy.location('pathname', { timeout: 4000 }).should('include', '/datasena_v2.0/inicio_sesion.html');
  });

  it('Enviar sin rol -> muestra alerta Rol no válido', () => {
    cy.on('window:alert', (txt) => {
      expect(txt).to.match(/Rol no válido/);
    });
    cy.get('select#rol').should('have.value', '');
    cy.get('input[name="usuario"]').clear().type('algouser');
    cy.get('input[name="password"]').clear().type('whatever');
    cy.get('button[type="submit"]').click();
    cy.location('pathname', { timeout: 4000 }).should('include', '/datasena_v2.0/inicio_sesion.html');
  });

});
