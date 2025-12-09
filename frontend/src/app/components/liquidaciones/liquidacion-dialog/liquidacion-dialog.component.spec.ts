import { ComponentFixture, TestBed } from '@angular/core/testing';

import { LiquidacionDialogComponent } from './liquidacion-dialog.component';

describe('LiquidacionDialogComponent', () => {
  let component: LiquidacionDialogComponent;
  let fixture: ComponentFixture<LiquidacionDialogComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [LiquidacionDialogComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(LiquidacionDialogComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
