import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PropietarioDialogComponent } from './propietario-dialog.component';

describe('PropietarioDialogComponent', () => {
  let component: PropietarioDialogComponent;
  let fixture: ComponentFixture<PropietarioDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PropietarioDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PropietarioDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
