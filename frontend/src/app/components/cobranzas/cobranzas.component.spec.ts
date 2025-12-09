import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CobranzasComponent } from './cobranzas.component';

describe('CobranzasComponent', () => {
  let component: CobranzasComponent;
  let fixture: ComponentFixture<CobranzasComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CobranzasComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CobranzasComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
