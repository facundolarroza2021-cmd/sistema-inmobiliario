import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CobroDialogComponent } from './cobro-dialog.component';

describe('CobroDialogComponent', () => {
  let component: CobroDialogComponent;
  let fixture: ComponentFixture<CobroDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CobroDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(CobroDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
